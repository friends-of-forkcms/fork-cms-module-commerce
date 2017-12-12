<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress;

use Backend\Modules\Catalog\Domain\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_order_addresses")
 * @ORM\Entity(repositoryClass="OrderAddressRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderAddress
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $first_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $last_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $email_address;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $house_number;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $house_number_addition;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $zip_code;

    private function __construct(
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

    public static function fromDataTransferObject(OrderAddressDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingOrderAddress()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderAddressDataTransferObject $dataTransferObject): self
    {
        return new self(
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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->email_address;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getHouseNumber(): string
    {
        return $this->house_number;
    }

    /**
     * @return string
     */
    public function getHouseNumberAddition(): ?string
    {
        return $this->house_number_addition;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zip_code;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->getFirstName() .' '. $this->getLastName();
    }

    private static function update(OrderAddressDataTransferObject $dataTransferObject)
    {
        $orderAddress = $dataTransferObject->getOrderAddressEntity();

        return $orderAddress;
    }

    public function getDataTransferObject(): OrderAddressDataTransferObject
    {
        return new OrderAddressDataTransferObject($this);
    }
}
