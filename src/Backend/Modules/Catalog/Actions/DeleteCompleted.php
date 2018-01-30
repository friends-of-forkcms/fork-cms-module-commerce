<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;

/**
 * This action will delete a comment
 *
 * @author Tijs Verkoyen <tijs@verkoyen.eu>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Willem van Dam <w.vandam@jvdict.nl>
 */
class DeleteCompleted extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();
        BackendCatalogModel::deleteCompletedOrders();

        // item was deleted, so redirect
        $this->redirect(BackendModel::createURLForAction('orders') . '&report=deleted-completed#tabCompleted');
    }
}
