<?php

namespace Backend\Modules\Commerce\Domain\Vat\Exception;

use Exception;

class VatNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a Vat with id = "'.$id.'".');
    }
}
