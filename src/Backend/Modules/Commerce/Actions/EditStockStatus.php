<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\StockStatus\Exception\StockStatusNotFound;
use Backend\Modules\Commerce\Domain\StockStatus\Exception\StockStatusValueNotFound;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusType;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusRepository;
use Backend\Modules\Commerce\Domain\StockStatus\Command\UpdateStockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\Event\Updated;
use Symfony\Component\Form\Form;

/**
 * This is the edit stock-status-action, it will display a form to edit a stock status
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditStockStatus extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $stockStatus = $this->getStockStatus();

        $form = $this->getForm($stockStatus);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $stockStatus->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteStockStatus'
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('stockStatus', $stockStatus);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateStockStatus $updateStockStatus */
        $updateStockStatus = $this->updateStockStatus($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateStockStatus->getStockStatusEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report'    => 'edited',
                    'var'       => $updateStockStatus->title,
                    'highlight' => 'row-' . $updateStockStatus->getStockStatusEntity()->getId(),
                ]
            )
        );
    }

    private function getStockStatus(): StockStatus
    {
        /** @var StockStatusRepository stockStatusRepository */
        $stockStatusRepository = $this->get('commerce.repository.stock_status');

        try {
            return $stockStatusRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (StockStatusNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
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

    private function getForm(StockStatus $stockStatus): Form
    {
        $form = $this->createForm(
            StockStatusType::class,
            new UpdateStockStatus($stockStatus)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateStockStatus(Form $form): UpdateStockStatus
    {
        /** @var UpdateStockStatus $updateStockStatus */
        $updateStockStatus = $form->getData();

        // The command bus will handle the saving of the stock status in the database.
        $this->get('command_bus')->handle($updateStockStatus);

        return $updateStockStatus;
    }
}
