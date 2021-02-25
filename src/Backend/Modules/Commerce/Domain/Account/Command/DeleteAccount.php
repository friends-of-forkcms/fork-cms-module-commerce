<?php

namespace Backend\Modules\Commerce\Domain\Account\Command;

use Backend\Modules\Commerce\Domain\Account\Account;

final class DeleteAccount
{
    /** @var Account */
    public $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
