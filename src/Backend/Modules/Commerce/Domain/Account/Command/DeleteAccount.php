<?php

namespace Backend\Modules\Commerce\Domain\Account\Command;

use Backend\Modules\Commerce\Domain\Account\Account;

final class DeleteAccount
{
    public Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
