<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue\Event;

final class UpdatedSpecificationValue extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.specification_value.updated';
}
