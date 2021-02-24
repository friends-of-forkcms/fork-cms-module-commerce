<?php

namespace Backend\Modules\Commerce\Domain\ProductOption\Event;

final class CreatedProductOption extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.product_option.created';
}
