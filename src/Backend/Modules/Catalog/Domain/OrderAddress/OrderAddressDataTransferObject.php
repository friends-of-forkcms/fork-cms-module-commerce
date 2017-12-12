<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress;

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
     * @var string
     */
    public $first_name;

    /**
     * @var string
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
     */
    public $street;

    /**
     * @var string
     */
    public $house_number;

    /**
     * @var string
     */
    public $house_number_addition;

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $zip_code;

    public function __construct(OrderAddress $orderAddress = null)
    {
        $this->orderAddressEntity = $orderAddress;

        if ( ! $this->hasExistingOrderAddress()) {
            return;
        }

        $this->id                    = $orderAddress->getId();
        $this->first_name            = $orderAddress->getFirstName();
        $this->last_name             = $orderAddress->getLastName();
        $this->email_address         = $orderAddress->getEmailAddress();
        $this->phone                 = $orderAddress->getPhone();
        $this->street                = $orderAddress->getStreet();
        $this->house_number          = $orderAddress->getHouseNumber();
        $this->house_number_addition = $orderAddress->getHouseNumberAddition();
        $this->city                  = $orderAddress->getCity();
        $this->zip_code              = $orderAddress->getZipCode();
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
