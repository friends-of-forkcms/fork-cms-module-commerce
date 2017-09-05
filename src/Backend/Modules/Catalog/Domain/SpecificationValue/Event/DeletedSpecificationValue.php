<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue\Event;

final class DeletedSpecificationValue extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.specification_value.deleted';
}
