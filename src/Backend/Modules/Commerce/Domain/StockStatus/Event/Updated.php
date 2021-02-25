<?php

namespace Backend\Modules\Commerce\Domain\StockStatus\Event;

final class Updated extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'commerce.event.vat.updated';
}
