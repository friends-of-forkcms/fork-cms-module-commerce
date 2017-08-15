<?php

namespace Backend\Modules\Catalog\Domain\Order;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_orders")
 * @ORM\Entity(repositoryClass="OrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Order
{
    /**
     * Different order statuses
     */
    const STATUS_PENDING = 1;
    const STATUS_COMPLETED = 3;
    const STATUS_MODERATION = 2;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="status")
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, name="first_name")
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, name="last_name")
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, name="house_number")
     */
    private $houseNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, name="postal_code")
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $total;

    private function __construct(
        int $status,
        DateTime $date,
        string $email,
        string $firstName,
        string $lastName,
        string $address,
        string $postalCode,
        string $city,
        int $total
    ) {
        $this->status     = $status;
        $this->date       = $date;
        $this->email      = $email;
        $this->firstName  = $firstName;
        $this->lastName   = $lastName;
        $this->address    = $address;
        $this->postalCode = $postalCode;
        $this->city       = $city;
        $this->total      = $total;
    }

    public static function fromDataTransferObject(OrderDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingOrder()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->status,
            $dataTransferObject->date,
            $dataTransferObject->email,
            $dataTransferObject->fistName,
            $dataTransferObject->lastName,
            $dataTransferObject->address,
            $dataTransferObject->postalCode,
            $dataTransferObject->city,
            $dataTransferObject->total
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
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return integer
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    private static function update(OrderDataTransferObject $dataTransferObject)
    {
        $order = $dataTransferObject->getOrderEntity();

        $order->status     = $dataTransferObject->status;
        $order->date       = $dataTransferObject->date;
        $order->email      = $dataTransferObject->email;
        $order->firstName  = $dataTransferObject->firstName;
        $order->lastName   = $dataTransferObject->lastName;
        $order->address    = $dataTransferObject->address;
        $order->postalCode = $dataTransferObject->postalCode;
        $order->city       = $dataTransferObject->city;
        $order->total      = $dataTransferObject->total;

        return $order;
    }

    public function getDataTransferObject(): OrderDataTransferObject
    {
        return new OrderDataTransferObject($this);
    }
}
