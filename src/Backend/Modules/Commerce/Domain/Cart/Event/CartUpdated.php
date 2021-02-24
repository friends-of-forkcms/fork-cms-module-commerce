<?php

namespace Backend\Modules\Commerce\Domain\Cart\Event;

final class CartUpdated extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.cart.updated';
}
