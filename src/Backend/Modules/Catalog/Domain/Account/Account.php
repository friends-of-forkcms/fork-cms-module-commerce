<?php

namespace Backend\Modules\Catalog\Domain\Account;

use Backend\Modules\Catalog\Domain\Cart\Cart;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_account")
 * @ORM\Entity(repositoryClass="AccountRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Account
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
     * @var OrderAddress[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\OrderAddress\OrderAddress", mappedBy="account")
     */
    private $addresses;

    /**
     * @var Order[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Order\Order", mappedBy="account")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $orders;

    /**
     * @var Cart[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Cart\Cart", mappedBy="account")
     */
    private $carts;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $profile_id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $first_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $last_name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_on")
     */
    private $createdOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="edited_on")
     */
    private $editedOn;

    private function __construct(
        ?int $profile_id,
        string $email,
        ?string $phone,
        ?string $company_name,
        string $first_name,
        string $last_name
    )
    {
        $this->profile_id = $profile_id;
        $this->email = $email;
        $this->phone = $phone;
        $this->company_name = $company_name;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
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
        return $this->profile_id;
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
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
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
     * @return \DateTime
     */
    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    /**
     * @return \DateTime
     */
    public function getEditedOn(): \DateTime
    {
        return $this->editedOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->editedOn = new \DateTime();

        if (!$this->id) {
            $this->createdOn = $this->editedOn;
        }
    }

    public static function fromDataTransferObject(AccountCustomerDataTransferObject $dataTransferObject)
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

        $account->profile_id = $dataTransferObject->profile_id;
        $account->email = $dataTransferObject->email_address;
        $account->phone = $dataTransferObject->phone;
        $account->company_name = $dataTransferObject->company_name;
        $account->first_name = $dataTransferObject->first_name;
        $account->last_name = $dataTransferObject->last_name;

        return $account;
    }

    public function getFullName()
    {
        return $this->getFirstName() .' '. $this->getLastName();
    }
}
