<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_account")
 * @ORM\Entity(repositoryClass="AccountRepository")
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var Collection|OrderAddress[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress", mappedBy="account")
     */
    private Collection $addresses;

    /**
     * @var Collection|Order[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Order\Order", mappedBy="account")
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private Collection $orders;

    /**
     * @var Collection|Cart[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Cart\Cart", mappedBy="account")
     */
    private Collection $carts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $profileId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $companyName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $lastName;

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

    private function __construct(
        ?int $profileId,
        string $email,
        ?string $phone,
        ?string $companyName,
        string $firstName,
        string $lastName
    ) {
        $this->profileId = $profileId;
        $this->email = $email;
        $this->phone = $phone;
        $this->companyName = $companyName;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return OrderAddress[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return Cart[]
     */
    public function getCarts()
    {
        return $this->carts;
    }

    /**
     * @return int
     */
    public function getProfileId(): ?int
    {
        return $this->profileId;
    }

    public function getEmail(): string
    {
        return $this->email;
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
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public static function fromDataTransferObject(AccountCustomerDataTransferObject $dataTransferObject): Account
    {
        if ($dataTransferObject->hasExistingAccount()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    public function getDataTransferObject(): AccountCustomerDataTransferObject
    {
        return new AccountCustomerDataTransferObject($this);
    }

    private static function create(AccountCustomerDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->profile_id,
            $dataTransferObject->email_address,
            $dataTransferObject->phone,
            $dataTransferObject->company_name,
            $dataTransferObject->first_name,
            $dataTransferObject->last_name
        );
    }

    private static function update(AccountCustomerDataTransferObject $dataTransferObject)
    {
        $account = $dataTransferObject->getAccountEntity();

        $account->profileId = $dataTransferObject->profile_id;
        $account->email = $dataTransferObject->email_address;
        $account->phone = $dataTransferObject->phone;
        $account->companyName = $dataTransferObject->company_name;
        $account->firstName = $dataTransferObject->first_name;
        $account->lastName = $dataTransferObject->last_name;

        return $account;
    }

    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }
}
