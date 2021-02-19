<?php

namespace Backend\Modules\Catalog\Domain\ProductOption\Exception;

use Exception;

class ProductOptionNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the product option you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a product option with id = "' . $id . '".');
    }
}
