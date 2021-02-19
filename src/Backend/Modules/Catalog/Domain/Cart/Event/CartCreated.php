<?php

namespace Backend\Modules\Catalog\Domain\Cart\Event;

final class CartCreated extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.cart.created';
}
