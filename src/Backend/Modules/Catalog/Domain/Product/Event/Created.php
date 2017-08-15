<?php

namespace Backend\Modules\Catalog\Domain\Product\Event;

final class Created extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.product.created';
}
