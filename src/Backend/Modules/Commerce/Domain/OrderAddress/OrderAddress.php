<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Country\Country;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Table(name="commerce_order_addresses")
 * @ORM\Entity(repositoryClass="OrderAddressRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class OrderAddress
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Account\Account", inversedBy="addresses")
     * @ORM\JoinColumn(name="accountId", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Account $account;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Country\Country")
     * @ORM\JoinColumn(name="countryId", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?Country $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $companyName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $firstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $lastName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $emailAddress;

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
    private ?string $houseNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $houseNumberAddition;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $zipCode;

    private function __construct(
        ?Account $account,
        ?Country $country,
        ?string $companyName,
        ?string $firstName,
        ?string $lastName,
        ?string $emailAddress,
        ?string $phone,
        ?string $street,
        ?string $houseNumber,
        ?string $houseNumberAddition,
        ?string $city,
        ?string $zipCode
    ) {
        $this->account = $account;
        $this->country = $country;
        $this->companyName = $companyName;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->phone = $phone;
        $this->street = $street;
        $this->houseNumber = $houseNumber;
        $this->houseNumberAddition = $houseNumberAddition;
        $this->city = $city;
        $this->zipCode = $zipCode;
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
        return $this->companyName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
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
        return $this->houseNumber;
    }

    public function getHouseNumberAddition(): ?string
    {
        return $this->houseNumberAddition;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $updatedAt;

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
        $orderAddress->companyName = $dataTransferObject->company_name;
        $orderAddress->firstName = $dataTransferObject->first_name;
        $orderAddress->lastName = $dataTransferObject->last_name;
        $orderAddress->emailAddress = $dataTransferObject->email_address;
        $orderAddress->phone = $dataTransferObject->phone;
        $orderAddress->street = $dataTransferObject->street;
        $orderAddress->houseNumber = $dataTransferObject->house_number;
        $orderAddress->houseNumberAddition = $dataTransferObject->house_number_addition;
        $orderAddress->city = $dataTransferObject->city;
        $orderAddress->zipCode = $dataTransferObject->zip_code;

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
