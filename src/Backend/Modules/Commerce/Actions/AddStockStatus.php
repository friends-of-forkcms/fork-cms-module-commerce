<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Commerce\Domain\StockStatus\Command\CreateStockStatus;
use Backend\Modules\Commerce\Domain\StockStatus\Event\Created;
use Backend\Modules\Commerce\Domain\StockStatus\StockStatusType;
use Symfony\Component\Form\Form;

/**
 * This is the add stock-status-action, it will display a form to create a new stock status.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddStockStatus extends BackendBaseActionAdd
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $createStockStatus = $this->createStockStatus($form);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createStockStatus->getStockStatusEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var' => $createStockStatus->title,
                ]
            )
        );
    }

    private function createStockStatus(Form $form): CreateStockStatus
    {
        $createStockStatus = $form->getData();

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($createStockStatus);

        return $createStockStatus;
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

    private function getForm(): Form
    {
        $form = $this->createForm(
            StockStatusType::class,
            new CreateStockStatus()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
