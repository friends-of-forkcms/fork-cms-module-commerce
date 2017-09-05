<?php

namespace Backend\Modules\Catalog\Domain\Cart\Exception;

use Exception;

class CartNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a Cart with id = "' . $id . '".');
    }
}
