<?php

namespace Backend\Modules\Catalog\Domain\Order\EventListener;

use Backend\Modules\Catalog\Domain\Order\Command\UpdateOrder;
use Backend\Modules\Catalog\Domain\Order\Event\OrderGenerateInvoiceNumber;
use Common\ModulesSettings;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

final class GenerateInvoiceNumber
{
    /**
     * @var MessageBusSupportingMiddleware
     */
    private $commandBus;

    /**
     * @var ModulesSettings
     */
    private $modulesSettings;

    /**
     * @param MessageBusSupportingMiddleware $commandBus
     * @param ModulesSettings $modulesSettings
     */
    public function __construct(MessageBusSupportingMiddleware $commandBus, ModulesSettings $modulesSettings) {
        $this->commandBus = $commandBus;
        $this->modulesSettings = $modulesSettings;
    }

    public function onGenerateInvoiceNumber(OrderGenerateInvoiceNumber $event): void
    {
        $invoiceNumber = $this->modulesSettings->get('Catalog', 'next_invoice_number');

        $updateOrder = new UpdateOrder($event->getOrder());

        if (!$updateOrder->invoiceNumber) {
            $updateOrder->invoiceNumber = $invoiceNumber;
            $updateOrder->invoiceDate = new \DateTime();

            $this->commandBus->handle($updateOrder);

            // Update the existing invoice number
            $this->modulesSettings->set(
                'Catalog',
                'next_invoice_number',
                ++$invoiceNumber
            );
        }
    }
}
