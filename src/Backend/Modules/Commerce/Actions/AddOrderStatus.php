<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Commerce\Domain\OrderStatus\Command\CreateOrderStatus;
use Backend\Modules\Commerce\Domain\OrderStatus\Event\Created;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusType;
use Symfony\Component\Form\Form;

/**
 * This is the add order-status-action, it will display a form to create a new order status.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddOrderStatus extends BackendBaseActionAdd
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

        $createOrderStatus = $this->createOrderStatus($form);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createOrderStatus->getOrderStatusEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var' => $createOrderStatus->title,
                ]
            )
        );
    }

    private function createOrderStatus(Form $form): CreateOrderStatus
    {
        $createOrderStatus = $form->getData();

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($createOrderStatus);

        return $createOrderStatus;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'OrderStatuses',
            null,
            null,
            $parameters
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            OrderStatusType::class,
            new CreateOrderStatus()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
