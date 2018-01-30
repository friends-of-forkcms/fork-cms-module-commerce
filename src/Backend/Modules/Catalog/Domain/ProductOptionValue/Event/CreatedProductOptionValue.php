<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue\Event;

final class CreatedProductOptionValue extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.product_option_value.created';
}
