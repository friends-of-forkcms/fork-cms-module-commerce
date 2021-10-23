<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Country\Country;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Table(name="commerce_order_addresses")
 * @ORM\Entity(repositoryClass="OrderAddressRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class OrderAddress
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Account\Account", inversedBy="addresses")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Account $account;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Country\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Country $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $company_name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $first_name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $last_name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $email_address;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $street;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $house_number;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $house_number_addition;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $zip_code;

    private function __construct(
        ?Account $account,
        ?Country $country,
        ?string $company_name,
        ?string $first_name,
        ?string $last_name,
        ?string $email_address,
        ?string $phone,
        ?string $street,
        ?string $house_number,
        ?string $house_number_addition,
        ?string $city,
        ?string $zip_code
    ) {
        $this->account = $account;
        $this->country = $country;
        $this->company_name = $company_name;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email_address = $email_address;
        $this->phone = $phone;
        $this->street = $street;
        $this->house_number = $house_number;
        $this->house_number_addition = $house_number_addition;
        $this->city = $city;
        $this->zip_code = $zip_code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function getEmailAddress(): ?string
    {
        return $this->email_address;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getHouseNumber(): ?string
    {
        return $this->house_number;
    }

    public function getHouseNumberAddition(): ?string
    {
        return $this->house_number_addition;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    public static function fromDataTransferObject(OrderAddressDataTransferObject $dataTransferObject): OrderAddress
    {
        if ($dataTransferObject->hasExistingOrderAddress()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderAddressDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->account,
            $dataTransferObject->country,
            $dataTransferObject->company_name,
            $dataTransferObject->first_name,
            $dataTransferObject->last_name,
            $dataTransferObject->email_address,
            $dataTransferObject->phone,
            $dataTransferObject->street,
            $dataTransferObject->house_number,
            $dataTransferObject->house_number_addition,
            $dataTransferObject->city,
            $dataTransferObject->zip_code
        );
    }

    private static function update(OrderAddressDataTransferObject $dataTransferObject): OrderAddress
    {
        $orderAddress = $dataTransferObject->getOrderAddressEntity();
        $orderAddress->country = $dataTransferObject->country;
        $orderAddress->company_name = $dataTransferObject->company_name;
        $orderAddress->first_name = $dataTransferObject->first_name;
        $orderAddress->last_name = $dataTransferObject->last_name;
        $orderAddress->email_address = $dataTransferObject->email_address;
        $orderAddress->phone = $dataTransferObject->phone;
        $orderAddress->street = $dataTransferObject->street;
        $orderAddress->house_number = $dataTransferObject->house_number;
        $orderAddress->house_number_addition = $dataTransferObject->house_number_addition;
        $orderAddress->city = $dataTransferObject->city;
        $orderAddress->zip_code = $dataTransferObject->zip_code;

        return $orderAddress;
    }

    public function getFullName(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getDataTransferObject(): OrderAddressDataTransferObject
    {
        return new OrderAddressDataTransferObject($this);
    }
}
