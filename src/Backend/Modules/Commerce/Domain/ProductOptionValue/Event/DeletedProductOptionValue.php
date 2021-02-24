<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue\Event;

final class DeletedProductOptionValue extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.product_option_value.deleted';
}
