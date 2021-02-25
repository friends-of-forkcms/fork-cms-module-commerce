<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Event;

final class DeletedProductOptionValue extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'commerce.event.product_option_value.deleted';
}
