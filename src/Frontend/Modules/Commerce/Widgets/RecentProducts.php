<?php

namespace Frontend\Modules\Commerce\Widgets;

use Backend\Modules\Commerce\Domain\Product\ProductRepository;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Language\Locale;

/**
 * This is a widget with recent products.
 */
class RecentProducts extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    private function parse(): void
    {
        // get list of recent products
        $numItems = $this->get('fork.settings')->get('Commerce', 'recent_products_full_num_items', 8);
        $recentProducts = $this->getProductRepository()->getMostRecent($numItems, Locale::frontendLanguage());

        $this->template->assign('recentProducts', $recentProducts);

        // Price VAT setting
        $this->template->assign('includeVAT', $this->get('fork.settings')->get('Commerce', 'show_prices_with_vat', true));
    }

    private function getProductRepository(): ProductRepository
    {
        return $this->get('commerce.repository.product');
    }
}
