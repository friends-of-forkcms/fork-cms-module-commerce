<?php

namespace Frontend\Modules\Commerce\Widgets;

use Backend\Modules\Commerce\Domain\Brand\BrandRepository;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Locale;

/**
 * This is a widget with the Commerce-brands.
 */
class Brands extends FrontendBaseWidget
{
    /**
     * Execute the extra.
     */
    public function execute(): void
    {
        parent::execute();

        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse.
     */
    private function parse()
    {
        /**
         * @var BrandRepository
         */
        $brandRepository = $this->get('commerce.repository.brand');

        $this->template->assign('brands', $brandRepository->findByLocale(Locale::frontendLanguage()));
        $this->template->assign('baseUrl', Navigation::getURLForBlock('Commerce', 'Brand'));
    }
}
