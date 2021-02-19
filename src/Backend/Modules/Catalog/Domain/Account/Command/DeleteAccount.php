<?php

namespace Backend\Modules\Catalog\Domain\Account\Command;

use Backend\Modules\Catalog\Domain\Account\Account;

final class DeleteAccount
{
    /** @var Account */
    public $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
