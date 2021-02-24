<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Symfony\Component\Validator\Constraints as Assert;

class AccountCustomerDataTransferObject
{
    public Account $accountEntity;
    public int $id;
    public ?int $profile_id;
    public ?string $company_name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $first_name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $last_name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Email(
     *     message="err.EmailIsInvalid",
     *     checkMX=true
     * )
     */
    public string $email_address;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $phone;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"register"})
     */
    public string $password;

    /**
     * @Assert\Valid
     */
    public AccountAddressDataTransferObject $shipment_address;

    /**
     * @Assert\Valid(groups={"DifferentInvoiceAddress"})
     */
    public AccountAddressDataTransferObject $invoice_address;

    public bool $same_invoice_address = true;

    public function __construct(Account $account = null)
    {
        $this->accountEntity = $account;

        if (!$this->hasExistingAccount()) {
            return;
        }

        $this->id = $account->getId();
        $this->profile_id = $account->getProfileId();
        $this->company_name = $account->getCompanyName();
        $this->first_name = $account->getFirstName();
        $this->last_name = $account->getLastName();
        $this->email_address = $account->getEmail();
        $this->phone = $account->getPhone();
    }

    public function hasExistingAccount(): bool
    {
        return $this->accountEntity instanceof Account;
    }

    public function setAccountEntity(Account $accountEntity): void
    {
        $this->accountEntity = $accountEntity;
    }

    public function getAccountEntity(): Account
    {
        return $this->accountEntity;
    }
}
