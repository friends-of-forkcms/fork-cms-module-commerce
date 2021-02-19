<?php

namespace Backend\Modules\Catalog\Domain\Cart\Exception;

use Exception;

class CartNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the cart you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a cart with id = "' . $id . '".');
    }
}
