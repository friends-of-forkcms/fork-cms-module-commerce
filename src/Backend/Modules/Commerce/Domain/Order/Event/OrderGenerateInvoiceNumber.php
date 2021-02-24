<?php

namespace Backend\Modules\Commerce\Domain\Order\Event;

use Backend\Modules\Commerce\Domain\Order\Order;
use Symfony\Component\EventDispatcher\Event;

final class OrderGenerateInvoiceNumber extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.order.generate_invoice_number';

    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
