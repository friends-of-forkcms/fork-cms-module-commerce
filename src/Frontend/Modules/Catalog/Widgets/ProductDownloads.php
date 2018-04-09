<?php

namespace Frontend\Modules\Catalog\Widgets;

use Frontend\Modules\MediaLibrary\Widgets\Base\FrontendMediaWidget;

/**
 * This will show the product downloads
 */
class ProductDownloads extends FrontendMediaWidget
{
    public function execute(): void
    {
        // We need to have a MediaGroup to show this widget
        try {
            $this->loadData();
        } catch (\Exception $e) {
            return;
        }

        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }
}
