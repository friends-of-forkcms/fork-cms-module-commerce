<?php

namespace Backend\Modules\Commerce\Domain\Account\Command;

use Backend\Modules\Commerce\Domain\Account\AccountGuest;
use Backend\Modules\Commerce\Domain\Account\AccountGuestDataTransferObject;

final class CreateAddress extends AccountGuestDataTransferObject
{
    public function set(AccountGuest $accountGuest): void
    {
        $this->accountGuestEntity = $accountGuest;
    }
}
