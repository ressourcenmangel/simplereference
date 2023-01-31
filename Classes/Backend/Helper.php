<?php

declare(strict_types=1);

namespace Ressourcenmangel\Simplereference\Backend;

use Doctrine\DBAL\DBALException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Clipboard\Clipboard;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class Helper
{
    /**
     * Processes all AJAX calls and returns a JSON formatted string
     *
     * Possible important incoming array values in $request->getParsedBody():
     *
     * Add new reference on top of a normal colPos
     * [addPanelId] = colpos-1-page-50-62c20b4d2e903078051133 // insert panel HTML Id
     * [addColpos] => 1 // colPos
     * [addPageUid] => 50 // page uid
     * [addLanguageuid] => 0 // sys_language_uid
     *
     * Add new reference on top of a container element
     * [addPanelId] = colpos-201-page-50-62c20b4d2e903078051133 // insert panel HTML Id
     * [addColpos] => 283-201
     * [addPageUid] => 50 // page uid
     * [addLanguageuid] => 0
     *
     * Add a new reference below existing content element 'addUid' : contentElementUid
     * [addPanelId] = colpos-201-page-50-62c20b4d2e903078051133 // insert panel HTML Id
     * [addTable] => tt_content
     * [addUid] => 281
     * [addPageUid] => 50 // page uid
     * [addLanguageuid] => 0
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws RouteNotFoundException
     */
    public function getData(ServerRequestInterface $request): ResponseInterface
    {
        $data = ['info' => 'null'];
        $beUser = $this->getBackendUser();

        // no backend user? return...
        if (!is_object($beUser)) {
            return new JsonResponse($data);
        }

        // the incoming get or post
        $parsedBody = $request->getParsedBody();
        $references = $this->getReferences($request);

        // early return if we have nothing to add
        if (!count($references)) {
            return new JsonResponse($data);
        }
        $data['references'] = $references;


        if (isset($parsedBody['addReference'])) {

            $records = implode(',', $references);
            $newUid = (microtime(true) * 10000);
            $infoArray = GeneralUtility::intExplode('-',$parsedBody['addPanelId'],true);

            if (isset($parsedBody['addTable'])) {
                $pid = '-' . $parsedBody['addUid'];
            } else {
                $pid = $parsedBody['addPageUid'];
            }

            $data['data'] = [
                'tt_content' => [
                    'NEW' . $newUid => [
                        'CType' => 'shortcut',
                        'header' => 'Reference',
                        //'header_layout' => '100',
                        'colPos' => (int)$infoArray[1], //end($colPosArray),
                        'sys_language_uid' => (int)$parsedBody['addLanguageuid'],
                        'pid' => (int)$pid,
                        'records' => $records,
                    ],
                ],
            ];

            // add EXT:container parent
            if (isset($parsedBody['addTable'])) {
                $currentRecord = $this->getRecordByUid((int)$parsedBody['addUid']);
                if (array_key_exists('tx_container_parent', $currentRecord)) {
                    $data['data']['tt_content']['NEW' . $newUid]['tx_container_parent'] = $currentRecord['tx_container_parent'];
                }
            }

            // or override if we are on top of a EXT:container colPos
            if (isset($parsedBody['addColpos'])) {
                $colPosArray = GeneralUtility::intExplode('-', $parsedBody['addColpos'], true);
                if (count($colPosArray) === 2) {
                    $data['data']['tt_content']['NEW' . $newUid]['tx_container_parent'] = $colPosArray[0];
                }
            }

            // create redirect uri
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $route = 'web_layout';
            $parameters = [
                'id' => $parsedBody['addPageUid'],
                't' => time(),
            ];
            $referenceType = '';
            $data['redirect'] = $uriBuilder->buildUriFromRoute($route, $parameters, $referenceType) . '#';

            // update info
            $data['info'] = 'success';
        }

        return new JsonResponse($data);
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    private function getReferences(ServerRequestInterface $request): array
    {
        $clipBoard = GeneralUtility::makeInstance(Clipboard::class);
        $clipBoard->initializeClipboard($request);
        // yes, normal pad can contain only one record, but why not prepare for other pads, too
        $clipBoardNormal = $clipBoard->clipData['normal'];

        $references = [];
        if (isset($clipBoardNormal['el']) && count($clipBoardNormal['el'])) {
            foreach ($clipBoardNormal['el'] as $key => $val) {
                $tableAndUid = explode('|', $key);
                if ($tableAndUid[0] === 'tt_content') {
                    $referenceCe = $this->getRecordByUid((int)$tableAndUid[1]);
                    if ($referenceCe) {
                        // we don't want Matroschka shortcuts
                        if ($referenceCe['CType'] === 'shortcut') {
                            $references[] = $referenceCe['records'];
                        } else {
                            $references[] = $tableAndUid[0] . '_' . $tableAndUid[1];
                        }
                    }
                }
            }
        }

        return $references;
    }

    /**
     * Get the record where the reference
     *
     * @param int $uid
     * @return array
     * @throws DBALException
     */
    protected function getRecordByUid(int $uid = 0): array
    {
        $data = [];

        if ($uid) {
            $table = 'tt_content';
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);
            // we need hidden ... records, too
            $queryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $data = $queryBuilder
                ->select('*')
                ->from($table)
                ->where('uid = :theUid')
                ->setParameter('theUid', $uid)
                ->execute()
                ->fetch();
        }

        return $data;
    }


    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
