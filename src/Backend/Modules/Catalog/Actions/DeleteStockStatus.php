<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\StockStatus\Exception\StockStatusNotFound;
use Backend\Modules\Catalog\Domain\StockStatus\StockStatus;
use Backend\Modules\Catalog\Domain\StockStatus\Event\Deleted;
use Backend\Modules\Catalog\Domain\StockStatus\Command\Delete as DeleteCommand;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a stock status
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class DeleteStockStatus extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute(): void
    {

        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());
        if ( ! $deleteForm->isSubmitted() || ! $deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $stockStatus = $this->getStockStatus((int)$deleteFormData['id']);

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
            return $this->get('catalog.repository.stock_status')->findOneByIdAndLocale(
                $id,
                Locale::workingLocale()
            );
        } catch (StockStatusNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}