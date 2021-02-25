<?php

namespace Backend\Modules\Commerce\Domain\ProductOption\Event;

final class UpdatedProductOption extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'commerce.event.product_option.updated';
}
