<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Commerce\Engine\Model as BackendCommerceModel;

/**
 * This action will delete a comment.
 */
class DeleteCompleted extends BackendBaseActionDelete
{
    public function execute(): void
    {
        parent::execute();
        BackendCommerceModel::deleteCompletedOrders();

        // item was deleted, so redirect
        $this->redirect(BackendModel::createURLForAction('orders') . '&report=deleted-completed#tabCompleted');
    }
}
