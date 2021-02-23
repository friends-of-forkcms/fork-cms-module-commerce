<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_cart_rules")
 * @ORM\Entity(repositoryClass="CartRuleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CartRule
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
     * @var Locale
     *
     * @ORM\Column(type="locale", name="language")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="from_date")
     */
    private $from;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="till_date", nullable=true)
     */
    private $till;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity_per_user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $minimum_amount;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=1, nullable=true)
     */
    private $reduction_percentage;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $reduction_amount;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $hidden;

    private function __construct(
        Locale $locale,
        string $title,
        \DateTime $from,
        ?\DateTime $till,
        int $quantity,
        ?int $quantity_per_user,
        string $code,
        ?float $minimum_amount,
        ?float $reduction_percentage,
        ?float $reduction_amount,
        bool $hidden
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->from = $from;
        $this->till = $till;
        $this->quantity = $quantity;
        $this->quantity_per_user = $quantity_per_user;
        $this->code = $code;
        $this->minimum_amount = $minimum_amount;
        $this->reduction_percentage = $reduction_percentage;
        $this->reduction_amount = $reduction_amount;
        $this->hidden = $hidden;
    }

    public static function fromDataTransferObject(CartRuleDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingCartRule()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(CartRuleDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->locale,
            $dataTransferObject->title,
            $dataTransferObject->from,
            $dataTransferObject->till,
            $dataTransferObject->quantity,
            $dataTransferObject->quantity_per_user,
            $dataTransferObject->code,
            $dataTransferObject->minimum_amount,
            $dataTransferObject->reduction_percentage,
            $dataTransferObject->reduction_amount,
            $dataTransferObject->hidden
        );
    }

    private static function update(CartRuleDataTransferObject $dataTransferObject)
    {
        $cartRule = $dataTransferObject->getCartRuleEntity();

        $cartRule->locale = $dataTransferObject->locale;
        $cartRule->title = $dataTransferObject->title;
        $cartRule->from = $dataTransferObject->from;
        $cartRule->till = $dataTransferObject->till;
        $cartRule->quantity = $dataTransferObject->quantity;
        $cartRule->quantity_per_user = $dataTransferObject->quantity_per_user;
        $cartRule->code = $dataTransferObject->code;
        $cartRule->minimum_amount = $dataTransferObject->minimum_amount;
        $cartRule->reduction_percentage = $dataTransferObject->reduction_percentage;
        $cartRule->reduction_amount = $dataTransferObject->reduction_amount;
        $cartRule->hidden = $dataTransferObject->hidden;

        return $cartRule;
    }

    public function getDataTransferObject(): CartRuleDataTransferObject
    {
        return new CartRuleDataTransferObject($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return \DateTime
     */
    public function getFrom(): \DateTime
    {
        return $this->from;
    }

    /**
     * @return \DateTime
     */
    public function getTill(): ?\DateTime
    {
        return $this->till;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getQuantityPerUser(): ?int
    {
        return $this->quantity_per_user;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return float
     */
    public function getMinimumAmount(): ?float
    {
        return $this->minimum_amount;
    }

    /**
     * @return float
     */
    public function getReductionPercentage(): ?float
    {
        return $this->reduction_percentage;
    }

    /**
     * @return float
     */
    public function getReductionAmount(): ?float
    {
        return $this->reduction_amount;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
