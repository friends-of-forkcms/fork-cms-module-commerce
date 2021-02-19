<?php

namespace Backend\Modules\Catalog\Domain\Account\Event;

use Backend\Modules\Catalog\Domain\Account\Account;
use Symfony\Component\EventDispatcher\Event as EventDispatcher;

abstract class Event extends EventDispatcher
{
    /** @var Account */
    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }
}
