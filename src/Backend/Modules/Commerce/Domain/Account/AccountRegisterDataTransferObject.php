<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Backend\Modules\Commerce\Domain\Account\Command\CreateShipmentAddress;
use Symfony\Component\Validator\Constraints as Assert;

class AccountRegisterDataTransferObject extends AddressDataTransferObject
{
    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Email(
     *     message="err.EmailIsRequired",
     *     checkMX=true
     * )
     */
    public string $email_address;

    public bool $same_shipping_address = true;

    public $password;

    public function set(AccountGuest $accountGuest): void
    {
        $this->accountGuestEntity = $accountGuest;
    }

    public function toShipmentAddress(): CreateShipmentAddress
    {
        $shipmentAddress = new CreateShipmentAddress();
        $shipmentAddress->company_name = $this->company_name;
        $shipmentAddress->first_name = $this->first_name;
        $shipmentAddress->last_name = $this->last_name;
        $shipmentAddress->phone = $this->phone;
        $shipmentAddress->email_address = $this->email_address;
        $shipmentAddress->street = $this->street;
        $shipmentAddress->house_number = $this->house_number;
        $shipmentAddress->house_number_addition = $this->house_number_addition;
        $shipmentAddress->city = $this->city;
        $shipmentAddress->zip_code = $this->zip_code;

        return $shipmentAddress;
    }
}
