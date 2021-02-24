<?php

namespace Frontend\Modules\Commerce\Widgets;

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\Commerce\Engine\Model as FrontendCommerceModel;

/**
 * This is a widget with recent products.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class RecentProducts extends FrontendBaseWidget
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
        // get list of recent products
        $numItems = $this->get('fork.settings')->get('Commerce', 'recent_products_full_num_items', 3);
        $recentProducts = FrontendCommerceModel::getAll($numItems);

        $this->tpl->assign('widgetCommerceRecentProducts', $recentProducts);
    }
}
