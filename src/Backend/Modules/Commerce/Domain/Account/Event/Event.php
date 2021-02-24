<?php

namespace Backend\Modules\Commerce\Domain\Account\Event;

use Backend\Modules\Commerce\Domain\Account\Account;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    private Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }
}
