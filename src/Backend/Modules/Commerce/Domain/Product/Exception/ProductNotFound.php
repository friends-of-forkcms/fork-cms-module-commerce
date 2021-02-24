<?php

namespace Backend\Modules\Commerce\Domain\Product\Exception;

use Exception;

class ProductNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the product you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a product with id = "'.$id.'".');
    }
}
