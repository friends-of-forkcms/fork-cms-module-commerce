<?php

namespace Backend\Modules\Commerce\Domain\Product\Event;

final class BeforeDelete extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'commerce.event.product.before_delete';
}
