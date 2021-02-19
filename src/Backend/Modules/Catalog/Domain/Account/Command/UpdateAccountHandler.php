<?php

namespace Backend\Modules\Catalog\Domain\Account\Command;

use Backend\Modules\Catalog\Domain\Account\Account;
use Backend\Modules\Catalog\Domain\Account\AccountRepository;

final class UpdateAccountHandler
{
    /** @var AccountRepository */
    private $accountRepository;

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
