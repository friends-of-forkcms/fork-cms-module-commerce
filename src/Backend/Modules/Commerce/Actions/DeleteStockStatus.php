<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\StockStatus\Command\DeleteStockStatus as DeleteCommand;
use Backend\Modules\Commerce\Domain\StockStatus\Event\Deleted;
use Backend\Modules\Commerce\Domain\StockStatus\Exception\StockStatusNotFound;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a stock status.
 */
class DeleteStockStatus extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $stockStatus = $this->getStockStatus((int) $deleteFormData['id']);

        try {
            // The command bus will handle the saving of the content block in the database.
            $this->get('command_bus')->handle(new DeleteCommand($stockStatus));

            $this->get('event_dispatcher')->dispatch(
                Deleted::EVENT_NAME,
                new Deleted($stockStatus)
            );

            $this->redirect($this->getBackLink(['report' => 'deleted', 'var' => $stockStatus->getTitle()]));
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->redirect($this->getBackLink(['error' => 'products-connected']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'StockStatuses',
            null,
            null,
            $parameters
        );
    }

    private function getStockStatus(int $id): StockStatus
    {
        try {
            return $this->get('commerce.repository.stock_status')->findOneByIdAndLocale(
                $id,
                Locale::workingLocale()
            );
        } catch (StockStatusNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}
