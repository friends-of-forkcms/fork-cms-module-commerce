<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Country\Country;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
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
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Account\Account", inversedBy="addresses")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $account;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Country\Country", inversedBy="addresses")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $company_name;

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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Account
     */
    public function getAccount(): ?Account
    {
        return $this->account;
    }

    /**
     * @return Country
     */
    public function getCountry(): Country
    {
        return $this->country;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    /**
     * @return string
     */
    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    /**
     * @param string $company_name
     */
    public function setCompanyName(string $company_name): void
    {
        $this->company_name = $company_name;
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
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): ?string
    {
        return $this->email_address;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getHouseNumber(): ?string
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
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getZipCode(): ?string
    {
        return $this->zip_code;
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

    private static function update(OrderAddressDataTransferObject $dataTransferObject)
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

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->getFirstName() .' '. $this->getLastName();
    }

    public function getDataTransferObject(): OrderAddressDataTransferObject
    {
        return new OrderAddressDataTransferObject($this);
    }
}
