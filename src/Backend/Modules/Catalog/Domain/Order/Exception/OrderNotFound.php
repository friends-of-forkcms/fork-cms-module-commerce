<?php

namespace Backend\Modules\Catalog\Domain\Order\Exception;

use Exception;

class OrderNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the order you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a order with id = "' . $id . '".');
    }
}
