<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Currency;
use Money\Money;

/**
 * @ORM\Table(name="commerce_cart_rules")
 * @ORM\Entity(repositoryClass="CartRuleRepository")
 */
class CartRule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $fromDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $tillDate;

    /**
     * @ORM\Column(type="integer")
     */
    private int $quantity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $quantityPerUser;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $code;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private ?int $minimumPriceAmount;

    /**
     * @ORM\Column(type="string", length=3, nullable=true, options={"collation":"utf8mb4_unicode_ci", "charset":"utf8mb4"})
     */
    private ?string $minimumPriceCurrencyCode;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=1, nullable=true)
     */
    private ?float $reductionPercentage;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private ?int $reductionPriceAmount;

    /**
     * @ORM\Column(type="string", length=3, nullable=true, options={"collation":"utf8mb4_unicode_ci", "charset":"utf8mb4"})
     */
    private ?string $reductionPriceCurrencyCode;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $hidden;

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
        Locale $locale,
        string $title,
        DateTimeInterface $fromDate,
        ?DateTimeInterface $tillDate,
        int $quantity,
        ?int $quantityPerUser,
        string $code,
        ?Money $minimumPrice,
        ?float $reductionPercentage,
        ?Money $reductionPrice,
        bool $hidden
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->fromDate = $fromDate;
        $this->tillDate = $tillDate;
        $this->quantity = $quantity;
        $this->quantityPerUser = $quantityPerUser;
        $this->code = $code;
        $this->reductionPercentage = $reductionPercentage;
        $this->hidden = $hidden;

        if ($minimumPrice !== null) {
            $this->minimumPriceAmount = $minimumPrice->getAmount();
            $this->minimumPriceCurrencyCode = 'EUR';
        }

        if ($reductionPrice !== null) {
            $this->reductionPriceAmount = $reductionPrice->getAmount();
            $this->reductionPriceCurrencyCode = 'EUR';
        }
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
            $dataTransferObject->minimum_price,
            $dataTransferObject->reduction_percentage,
            $dataTransferObject->reduction_price,
            $dataTransferObject->hidden
        );
    }

    private static function update(CartRuleDataTransferObject $dataTransferObject): CartRule
    {
        $cartRule = $dataTransferObject->getCartRuleEntity();

        $cartRule->locale = $dataTransferObject->locale;
        $cartRule->title = $dataTransferObject->title;
        $cartRule->fromDate = $dataTransferObject->from;
        $cartRule->tillDate = $dataTransferObject->till;
        $cartRule->quantity = $dataTransferObject->quantity;
        $cartRule->quantityPerUser = $dataTransferObject->quantity_per_user;
        $cartRule->code = $dataTransferObject->code;
        $cartRule->reductionPercentage = $dataTransferObject->reduction_percentage;
        $cartRule->hidden = $dataTransferObject->hidden;

        if ($dataTransferObject->minimum_price !== null) {
            $cartRule->minimumPriceAmount = $dataTransferObject->minimum_price->getAmount();
            $cartRule->minimumPriceCurrencyCode = 'EUR';
        }

        if ($dataTransferObject->reduction_price !== null) {
            $cartRule->reductionPriceAmount = $dataTransferObject->reduction_price->getAmount();
            $cartRule->reductionPriceCurrencyCode = 'EUR';
        }

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

    public function getFromDate(): DateTimeInterface
    {
        return $this->fromDate;
    }

    public function getTillDate(): ?DateTimeInterface
    {
        return $this->tillDate;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getQuantityPerUser(): ?int
    {
        return $this->quantityPerUser;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Doctrine does not have nullable embeddables (Money type). We have to define the fields manually and turn them
     * into the embeddable object manually: https://stackoverflow.com/a/45262491/1409047
     */
    public function getMinimumPrice(): ?Money
    {
        if ($this->minimumPriceAmount === null || $this->minimumPriceCurrencyCode === null) {
            return null;
        }

        return new Money($this->minimumPriceAmount, new Currency($this->minimumPriceCurrencyCode));
    }

    public function getReductionPercentage(): ?float
    {
        return $this->reductionPercentage;
    }

    public function getHumanReadableReductionPercentage(): ?string
    {
        if ($this->reductionPercentage === null) {
            return null;
        }

        return $this->getReductionPercentage() * 100 . '%';
    }

    /**
     * Doctrine does not have nullable embeddables (Money type). We have to define the fields manually and turn them
     * into the embeddable object manually: https://stackoverflow.com/a/45262491/1409047
     */
    public function getReductionPrice(): ?Money
    {
        if ($this->reductionPriceAmount === null || $this->reductionPriceCurrencyCode === null) {
            return null;
        }

        return new Money($this->reductionPriceAmount, new Currency($this->reductionPriceCurrencyCode));
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
