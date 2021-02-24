<?php

namespace Backend\Modules\Commerce\Domain\Product\Event;

use Backend\Modules\Commerce\Domain\Product\Product;

final class Deleted extends Event
{
    /**
     * @var string the name the listener needs to listen to to catch this event
     */
    public const EVENT_NAME = 'commerce.event.product.deleted';

    private int $id;

    public function __construct(Product $product, int $id)
    {
        $this->id = $id;

        parent::__construct($product);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
