<?php

namespace Backend\Modules\Commerce\Domain\CartRule\Event;

final class CartRuleUpdated extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.cart_rule.updated';
}
