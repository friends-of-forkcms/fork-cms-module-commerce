<?php

namespace Backend\Modules\Catalog\Domain\Product\Exception;

use Exception;

class ProductNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a Product with id = "' . $id . '".');
    }
}
