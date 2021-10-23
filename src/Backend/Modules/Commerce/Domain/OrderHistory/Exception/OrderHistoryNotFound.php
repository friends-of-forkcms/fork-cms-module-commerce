<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory\Exception;

use Exception;

class OrderHistoryNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a OrderHistory with id = "' . $id . '".');
    }
}
