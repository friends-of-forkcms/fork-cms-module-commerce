<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Modules\Commerce\Domain\ShipmentMethod\DataGrid;

/**
 * This action allows you to enable, disable and edit a shipment method.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class ShipmentMethods extends BackendBaseActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $this->template->assign('dataGrid', DataGrid::getHtml());

        $this->parse();
        $this->display();
    }
}
