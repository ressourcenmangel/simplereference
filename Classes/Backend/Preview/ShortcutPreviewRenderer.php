<?php

declare(strict_types=1);

namespace Ressourcenmangel\Simplereference\Backend\Preview;

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\View\PageLayoutContext;

class ShortcutPreviewRenderer extends StandardContentPreviewRenderer
{
    /**
     * @var PageLayoutContext
     */
    protected $context;

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $content = parent::renderPageModulePreviewContent($item);

        return '<span class="simplereference-shortcut">' .  $content . '</span>';
    }
}
