<?php

namespace Backend\Modules\Commerce\Domain\Quote\Event;

final class QuoteCreated extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.quote.created';
}
