<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Commerce\Domain\Order\DataGridOrderHistory;
use Backend\Modules\Commerce\Domain\Order\DataGridProducts;
use Backend\Modules\Commerce\Domain\Order\Event\OrderUpdated;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\Order\OrderRepository;
use Backend\Modules\Commerce\Domain\Order\OrderType;
use Backend\Modules\Commerce\Domain\OrderHistory\Command\CreateOrderHistory;
use Symfony\Component\Form\Form;

/**
 * This is the edit-action, it will display a form to edit an existing item.
 */
class EditOrder extends BackendBaseActionEdit
{
    private Order $order;

    public function execute(): void
    {
        parent::execute();

        $this->order = $this->getOrder();

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('order', $this->order);
            $this->template->assign('dataGridOrderProducts', DataGridProducts::getHtml($this->order));
            $this->template->assign('dataGridOrderHistory', DataGridOrderHistory::getHtml($this->order));

            $this->header->addCSS('EditOrder.css');

            $this->parse();
            $this->display();

            return;
        }

        /** @var CreateOrderHistory $createOrderHistory */
        $createOrderHistory = $this->createOrderHistory($form);

        $this->get('event_dispatcher')->dispatch(
            OrderUpdated::EVENT_NAME,
            new OrderUpdated($this->order, $createOrderHistory->getOrderHistoryEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'edited',
                    'highlight' => 'row-' . $this->order->getId(),
                ]
            )
        );
    }

    private function createOrderHistory(Form $form): CreateOrderHistory
    {
        /** @var CreateOrderHistory $createOrderHistory */
        $createOrderHistory = $form->getData();
        $createOrderHistory->order = $this->order;

        // The command bus will handle the saving of the product in the database.
        $this->get('command_bus')->handle($createOrderHistory);

        return $createOrderHistory;
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            OrderType::class,
            new CreateOrderHistory()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getOrder(): Order
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->get('commerce.repository.order');

        try {
            return $orderRepository->findOneById($this->getRequest()->query->getInt('id'));
        } catch (OrderNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    /**
     * @throws \Exception
     */
    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Orders',
            null,
            null,
            $parameters
        );
    }
}
