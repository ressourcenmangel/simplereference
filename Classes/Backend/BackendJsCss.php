<?php
declare(strict_types=1);

namespace Ressourcenmangel\Simplereference\Backend;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendJsCss
{
    /**
     * @param $params array The already used JS and CSS files and the header and footer data
     * @param $ref mixed|object The back reference to the TYPO3\CMS\Core\Page\PageRenderer class
     */
    public function addJsCss($params, $ref): void
    {
        if (TYPO3_MODE == 'BE') {

            $typo3Version = (int)GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion();
            $ref->addCssFile('EXT:simplereference/Resources/Public/Backend/Css/styles.css');
            if ($typo3Version === 10) {
                $ref->addJsFooterFile('EXT:simplereference/Resources/Public/JavaScript/Main.v10.js');
            }
            if ($typo3Version === 11) {
                $ref->addJsFooterFile('EXT:simplereference/Resources/Public/JavaScript/Main.v11.js');
            }

            $ref->addInlineLanguageLabelFile('EXT:simplereference/Resources/Private/Language/locallang_modal.xlf');
        }
    }
}
