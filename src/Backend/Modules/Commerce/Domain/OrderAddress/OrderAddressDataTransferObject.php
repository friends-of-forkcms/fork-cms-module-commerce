<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Country\Country;
use Symfony\Component\Validator\Constraints as Assert;

class OrderAddressDataTransferObject
{
    protected ?OrderAddress $orderAddressEntity;
    public int $id;
    public ?Account $account;
    public ?Country $country;
    public ?string $company_name;

    /**
     * @ Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $first_name;

    /**
     * @ Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $last_name;
    public ?string $email_address;
    public ?string $phone;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $street;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $house_number;

    public ?string $house_number_addition;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $city;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public ?string $zip_code;

    public function __construct(OrderAddress $orderAddress = null)
    {
        $this->orderAddressEntity = $orderAddress;

        if (!$this->hasExistingOrderAddress()) {
            return;
        }

        $this->id = $this->orderAddressEntity->getId();
        $this->account = $this->orderAddressEntity->getAccount();
        $this->country = $this->orderAddressEntity->getCountry();
        $this->company_name = $this->orderAddressEntity->getCompanyName();
        $this->first_name = $this->orderAddressEntity->getFirstName();
        $this->last_name = $this->orderAddressEntity->getLastName();
        $this->email_address = $this->orderAddressEntity->getEmailAddress();
        $this->phone = $this->orderAddressEntity->getPhone();
        $this->street = $this->orderAddressEntity->getStreet();
        $this->house_number = $this->orderAddressEntity->getHouseNumber();
        $this->house_number_addition = $this->orderAddressEntity->getHouseNumberAddition();
        $this->city = $this->orderAddressEntity->getCity();
        $this->zip_code = $this->orderAddressEntity->getZipCode();
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
