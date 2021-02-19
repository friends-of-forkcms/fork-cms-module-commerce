<?php

namespace Backend\Modules\Catalog\Domain\Account;

use Backend\Modules\Catalog\Domain\OrderAddress\Command\CreateOrderAddress;
use Symfony\Component\Validator\Constraints as Assert;

class AccountAddressDataTransferObject
{
    /**
     * @var Account
     */
    public $account;

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
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $street;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $house_number;

    /**
     * @var string
     */
    public $house_number_addition;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $city;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $zip_code;

    /**
     * Generate an order address data transfer object based on these values
     *
     * @return CreateOrderAddress
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
