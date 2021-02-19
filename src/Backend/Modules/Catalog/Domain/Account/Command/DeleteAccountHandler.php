<?php

namespace Backend\Modules\Catalog\Domain\Account\Command;

use Backend\Modules\Catalog\Domain\Account\AccountRepository;

final class DeleteAccountHandler
{
    /** @var AccountRepository */
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function handle(DeleteAccount $deleteAccount): void
    {
        $this->accountRepository->removeById($deleteAccount->account->getId());
    }
}
