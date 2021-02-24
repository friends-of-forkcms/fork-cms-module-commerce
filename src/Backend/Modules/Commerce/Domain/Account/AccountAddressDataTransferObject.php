<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Backend\Modules\Commerce\Domain\OrderAddress\Command\CreateOrderAddress;
use Symfony\Component\Validator\Constraints as Assert;

class AccountAddressDataTransferObject
{
    public Account $account;
    public string $company_name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $first_name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $last_name;

    public string $email_address;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $phone;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $street;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $house_number;

    public string $house_number_addition;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $city;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $zip_code;

    /**
     * Generate an order address data transfer object based on these values.
     */
    public function toCommand(): CreateOrderAddress
    {
        $command = new CreateOrderAddress();
        $command->account = $this->account;
        $command->company_name = $this->company_name;
        $command->first_name = $this->first_name;
        $command->last_name = $this->last_name;
        $command->email_address = $this->email_address;
        $command->phone = $this->phone;
        $command->street = $this->street;
        $command->house_number = $this->house_number;
        $command->house_number_addition = $this->house_number_addition;
        $command->city = $this->city;
        $command->zip_code = $this->zip_code;

        return $command;
    }
}
