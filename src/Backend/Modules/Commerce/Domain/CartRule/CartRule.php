<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_cart_rules")
 * @ORM\Entity(repositoryClass="CartRuleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CartRule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="date", name="from_date")
     */
    private DateTimeInterface $from;

    /**
     * @ORM\Column(type="date", name="till_date", nullable=true)
     */
    private ?DateTimeInterface $till;

    /**
     * @ORM\Column(type="integer")
     */
    private int $quantity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $quantity_per_user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $code;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private ?float $minimum_amount;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=1, nullable=true)
     */
    private ?float $reduction_percentage;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private ?float $reduction_amount;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $hidden;

    private function __construct(
        Locale $locale,
        string $title,
        DateTimeInterface $from,
        ?DateTimeInterface $till,
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

    public static function fromDataTransferObject(CartRuleDataTransferObject $dataTransferObject): CartRule
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

    private static function update(CartRuleDataTransferObject $dataTransferObject): CartRule
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFrom(): DateTimeInterface
    {
        return $this->from;
    }

    public function getTill(): ?DateTimeInterface
    {
        return $this->till;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getQuantityPerUser(): ?int
    {
        return $this->quantity_per_user;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMinimumAmount(): ?float
    {
        return $this->minimum_amount;
    }

    public function getReductionPercentage(): ?float
    {
        return $this->reduction_percentage;
    }

    public function getReductionAmount(): ?float
    {
        return $this->reduction_amount;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
