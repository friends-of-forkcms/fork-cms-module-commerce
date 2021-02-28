<?php

namespace Backend\Modules\Commerce\Domain\Order\Event;

final class OrderDeleted extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.order.deleted';
}