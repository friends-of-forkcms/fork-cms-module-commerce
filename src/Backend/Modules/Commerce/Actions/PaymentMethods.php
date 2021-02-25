<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Modules\Commerce\Domain\PaymentMethod\DataGrid;

/**
 * This action allows you to enable, disable and edit a payment method
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class PaymentMethods extends BackendBaseActionIndex
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
