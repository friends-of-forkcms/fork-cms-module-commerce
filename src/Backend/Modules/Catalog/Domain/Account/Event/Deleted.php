<?php

namespace Backend\Modules\Catalog\Domain\Account\Event;

final class Deleted extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.account.deleted';
}
