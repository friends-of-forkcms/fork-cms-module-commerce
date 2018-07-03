<?php

namespace Backend\Modules\Catalog\Domain\OrderHistory\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Order\Event\OrderGenerateInvoiceNumber;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistoryRepository;
use Common\ModulesSettings;

final class CreateOrderHistoryHandler
{
    /** @var OrderHistoryRepository */
    private $orderHistoryRepository;

    /** @var ModulesSettings */
    private $modulesSettings;

    public function __construct(OrderHistoryRepository $orderHistoryRepository, ModulesSettings $modulesSettings)
    {
        $this->orderHistoryRepository = $orderHistoryRepository;
        $this->modulesSettings = $modulesSettings;
    }

    public function handle(CreateOrderHistory $createOrderHistory): void
    {
        $orderHistory = OrderHistory::fromDataTransferObject($createOrderHistory);
        $this->orderHistoryRepository->add($orderHistory);

        $generateInvoiceStatuses = $this->modulesSettings->get('Catalog', 'automatic_invoice_statuses', []);
        if (in_array($orderHistory->getOrderStatus()->getId(), $generateInvoiceStatuses)) {
            Model::get('event_dispatcher')->dispatch(
                OrderGenerateInvoiceNumber::EVENT_NAME,
                new OrderGenerateInvoiceNumber($createOrderHistory->order)
            );
        }

        $createOrderHistory->setOrderHistoryEntity($orderHistory);
    }
}
