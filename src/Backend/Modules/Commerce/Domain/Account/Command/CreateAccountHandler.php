<?php

namespace Backend\Modules\Commerce\Domain\Account\Command;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Account\AccountRepository;

final class CreateAccountHandler
{
    private AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function handle(CreateAccount $createAccount): void
    {
        $account = Account::fromDataTransferObject($createAccount);
        $this->accountRepository->add($account);

        $createAccount->setAccountEntity($account);
    }
}
