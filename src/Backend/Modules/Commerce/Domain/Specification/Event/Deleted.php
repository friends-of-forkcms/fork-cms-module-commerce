<?php

namespace Backend\Modules\Commerce\Domain\Specification\Event;

final class Deleted extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.specification.deleted';
}
