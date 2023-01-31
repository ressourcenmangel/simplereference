<?php

call_user_func(static function () {
    $fluidBasedPageModule = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\Features::class)->isFeatureEnabled('fluidBasedPageModule');
    if (false === $fluidBasedPageModule) {
        // implement Standard Preview renderer if you need it for older TYPO3 Versions,
        // or use Hook in ext_localconf.php;
        // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']
    } else {
        $GLOBALS['TCA']['tt_content']['types']['shortcut']['previewRenderer'] = \Ressourcenmangel\Simplereference\Backend\Preview\ShortcutPreviewRenderer::class;
    }
});
