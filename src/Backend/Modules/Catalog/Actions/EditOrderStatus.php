<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\OrderStatus\Exception\OrderStatusNotFound;
use Backend\Modules\Catalog\Domain\OrderStatus\Exception\OrderStatusValueNotFound;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatusType;
use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatusRepository;
use Backend\Modules\Catalog\Domain\OrderStatus\Command\UpdateOrderStatus;
use Backend\Modules\Catalog\Domain\OrderStatus\Event\Updated;
use Symfony\Component\Form\Form;

/**
 * This is the edit order-status-action, it will display a form to edit a order status
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditOrderStatus extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $orderStatus = $this->getOrderStatus();

        $form = $this->getForm($orderStatus);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $orderStatus->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteOrderStatus'
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('orderStatus', $orderStatus);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateOrderStatus $updateOrderStatus */
        $updateOrderStatus = $this->updateOrderStatus($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateOrderStatus->getOrderStatusEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report'    => 'edited',
                    'var'       => $updateOrderStatus->title,
                    'highlight' => 'row-' . $updateOrderStatus->getOrderStatusEntity()->getId(),
                ]
            )
        );
    }

    private function getOrderStatus(): OrderStatus
    {
        /** @var OrderStatusRepository orderStatusRepository */
        $orderStatusRepository = $this->get('catalog.repository.order_status');

        try {
            return $orderStatusRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (OrderStatusNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
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

    private function getForm(OrderStatus $orderStatus): Form
    {
        $form = $this->createForm(
            OrderStatusType::class,
            new UpdateOrderStatus($orderStatus)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateOrderStatus(Form $form): UpdateOrderStatus
    {
        /** @var UpdateOrderStatus $updateOrderStatus */
        $updateOrderStatus = $form->getData();

        // The command bus will handle the saving of the order status in the database.
        $this->get('command_bus')->handle($updateOrderStatus);

        return $updateOrderStatus;
    }
}
