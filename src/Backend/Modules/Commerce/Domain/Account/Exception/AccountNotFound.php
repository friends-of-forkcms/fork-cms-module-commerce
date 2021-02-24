<?php

namespace Backend\Modules\Commerce\Domain\Account\Exception;

use Exception;

class AccountNotFound extends Exception
{
    public static function forEmptyId(): self
    {
        return new self('The id for the account you have given is null');
    }

    public static function forId(string $id): self
    {
        return new self('Can\'t find a account with id = "'.$id.'".');
    }
}
