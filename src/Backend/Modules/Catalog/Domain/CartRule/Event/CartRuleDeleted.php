<?php

namespace Backend\Modules\Catalog\Domain\CartRule\Event;

final class CartRuleDeleted extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'catalog.event.cart_rule.deleted';
}
