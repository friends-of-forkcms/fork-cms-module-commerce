<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory\Event;

final class Created extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.order_history.created';
}
