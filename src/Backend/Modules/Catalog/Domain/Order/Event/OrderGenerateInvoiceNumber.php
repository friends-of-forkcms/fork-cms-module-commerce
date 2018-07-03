<?php

namespace Backend\Modules\Catalog\Domain\Order\Event;

use Backend\Modules\Catalog\Domain\Order\Order;
use Symfony\Component\EventDispatcher\Event;

final class OrderGenerateInvoiceNumber extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.order.generate_invoice_number';

    /** @var Order */
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
