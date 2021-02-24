<?php

namespace Backend\Modules\Commerce\Domain\Account\Command;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Account\AccountRepository;

final class UpdateAccountHandler
{
    private AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function handle(UpdateAccount $updateAccount): void
    {
        $account = Account::fromDataTransferObject($updateAccount);
        $this->accountRepository->add($account);

        $updateAccount->setAccountEntity($account);
    }
}
