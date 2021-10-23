<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus\Exception;

use Exception;

class OrderStatusNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a OrderStatus with id = "' . $id . '".');
    }
}
