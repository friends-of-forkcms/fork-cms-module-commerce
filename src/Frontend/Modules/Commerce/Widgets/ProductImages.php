<?php

namespace Frontend\Modules\Commerce\Widgets;

use Frontend\Modules\MediaLibrary\Widgets\Base\FrontendMediaWidget;

/**
 * This will show a MediaGroup (Custom Module) or a MediaGallery (Media Module) in a slider using BxSlider.
 */
class ProductImages extends FrontendMediaWidget
{
    public function execute(): void
    {
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
