<?php

namespace Backend\Modules\Catalog\Domain\Account\Command;

use Backend\Modules\Catalog\Domain\Account\AccountGuest;
use Backend\Modules\Catalog\Domain\Account\AccountGuestDataTransferObject;

final class CreateAddress extends AccountGuestDataTransferObject
{
    public function set(AccountGuest $accountGuest): void
    {
        $this->accountGuestEntity = $accountGuest;
    }
}
