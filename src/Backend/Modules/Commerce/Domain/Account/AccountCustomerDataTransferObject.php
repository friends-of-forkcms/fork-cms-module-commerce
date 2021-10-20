<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Symfony\Component\Validator\Constraints as Assert;

class AccountCustomerDataTransferObject
{
    public ?Account $accountEntity = null;
    public int $id;
    public ?int $profile_id = null;
    public ?string $company_name = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $first_name = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $last_name = null;

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
    public ?string $phone = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"register"})
     */
    public string $password;

    /**
     * @Assert\Valid
     */
    public $shipment_address;

    /**
     * @Assert\Valid(groups={"DifferentInvoiceAddress"})
     */
    public $invoice_address;

    public bool $same_invoice_address = true;

    public function __construct(Account $account = null)
    {
        $this->accountEntity = $account;

        if (!$this->hasExistingAccount()) {
            return;
        }

        $this->id = $this->accountEntity->getId();
        $this->profile_id = $this->accountEntity->getProfileId();
        $this->company_name = $this->accountEntity->getCompanyName();
        $this->first_name = $this->accountEntity->getFirstName();
        $this->last_name = $this->accountEntity->getLastName();
        $this->email_address = $this->accountEntity->getEmail();
        $this->phone = $this->accountEntity->getPhone();
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
