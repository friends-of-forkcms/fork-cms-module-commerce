<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Modules\Catalog\Domain\ShipmentMethod\DataGrid;

/**
 * This action allows you to enable, disable and edit a shipment method
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class ShipmentMethods extends BackendBaseActionIndex
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $this->template->assign('dataGrid', DataGrid::getHtml());

        $this->parse();
        $this->display();
    }
}
