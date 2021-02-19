<?php

namespace Backend\Modules\Catalog\Domain\Account\Command;

use Backend\Modules\Catalog\Domain\Account\Account;
use Backend\Modules\Catalog\Domain\Account\AccountRepository;

final class CreateAccountHandler
{
    /** @var AccountRepository */
    private $accountRepository;

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
