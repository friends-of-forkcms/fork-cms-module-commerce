<?php

namespace Backend\Modules\Catalog\Domain\Cart\Event;

final class CartUpdated extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.cart.updated';
}
