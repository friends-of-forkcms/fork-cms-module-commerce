<?php

namespace Backend\Modules\Catalog\Domain\Account;

use Symfony\Component\Validator\Constraints as Assert;

class AccountCustomerDataTransferObject
{
    /**
     * @var Account
     */
    public $accountEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $profile_id;

    /**
     * @var string
     */
    public $company_name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $first_name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $last_name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Email(
     *     message = "err.EmailIsInvalid",
     *     checkMX = true
     * )
     */
    public $email_address;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $phone;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"register"})
     */
    public $password;

    /**
     * @var AccountAddressDataTransferObject
     *
     * @Assert\Valid
     */
    public $shipment_address;

    /**
     * @var AccountAddressDataTransferObject
     *
     * @Assert\Valid(groups={"DifferentInvoiceAddress"})
     */
    public $invoice_address;

    /**
     * @var bool
     */
    public $same_invoice_address = true;

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
