<?php

namespace Backend\Modules\Catalog\Domain\Quote\Event;

final class QuoteCreated extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.quote.created';
}
