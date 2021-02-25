<?php

namespace Backend\Modules\Commerce\Domain\Cart\Event;

final class CartDeleted extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'commerce.event.cart.deleted';
}
