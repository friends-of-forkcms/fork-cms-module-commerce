<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_account")
 * @ORM\Entity(repositoryClass="AccountRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
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
     * @ORM\OrderBy({"date": "DESC"})
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
    private ?int $profile_id;

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
    private ?string $company_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $first_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $last_name;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdOn;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="edited_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $editedOn;

    private function __construct(
        ?int $profile_id,
        string $email,
        ?string $phone,
        ?string $company_name,
        string $first_name,
        string $last_name
    ) {
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

    public function getCreatedOn(): DateTimeInterface
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTimeInterface
    {
        return $this->editedOn;
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
        return $this->getFirstName() . ' ' . $this->getLastName();
    }
}
