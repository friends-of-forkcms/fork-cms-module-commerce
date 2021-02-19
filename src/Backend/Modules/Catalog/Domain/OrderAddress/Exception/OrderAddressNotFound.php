<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress\Exception;

use Exception;

class OrderAddressNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a Order Address with id = "' . $id . '".');
    }
}
