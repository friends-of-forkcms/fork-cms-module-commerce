<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Country\Country;
use Symfony\Component\Validator\Constraints as Assert;

class OrderAddressDataTransferObject
{
    /**
     * @var OrderAddress
     */
    protected $orderAddressEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var Account
     */
    public $account;

    /**
     * @var Country
     */
    public $country;

    /**
     * @var string
     */
    public $company_name;

    /**
     * @var string
     *
     * @ Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $first_name;

    /**
     * @var string
     *
     * @ Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $last_name;

    /**
     * @var string
     */
    public $email_address;

    /**
     * @var string
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

    public function __construct(OrderAddress $orderAddress = null)
    {
        $this->orderAddressEntity = $orderAddress;

        if (!$this->hasExistingOrderAddress()) {
            return;
        }

        $this->id = $orderAddress->getId();
        $this->account = $orderAddress->getAccount();
        $this->country = $orderAddress->getCountry();
        $this->company_name = $orderAddress->getCompanyName();
        $this->first_name = $orderAddress->getFirstName();
        $this->last_name = $orderAddress->getLastName();
        $this->email_address = $orderAddress->getEmailAddress();
        $this->phone = $orderAddress->getPhone();
        $this->street = $orderAddress->getStreet();
        $this->house_number = $orderAddress->getHouseNumber();
        $this->house_number_addition = $orderAddress->getHouseNumberAddition();
        $this->city = $orderAddress->getCity();
        $this->zip_code = $orderAddress->getZipCode();
    }

    public function setOrderAddressEntity(OrderAddress $orderAddressEntity): void
    {
        $this->orderAddressEntity = $orderAddressEntity;
    }

    public function getOrderAddressEntity(): OrderAddress
    {
        return $this->orderAddressEntity;
    }

    public function hasExistingOrderAddress(): bool
    {
        return $this->orderAddressEntity instanceof OrderAddress;
    }
}
