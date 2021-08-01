<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;

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
     * @ORM\Column(type="bigint", nullable=true)
     */
    private ?int $minimum_price_amount;

    /**
     * @ORM\Column(type="string", length=3, nullable=true, options={"collation":"utf8mb4_unicode_ci", "charset":"utf8mb4"})
     */
    private ?string $minimum_price_currency_code;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=1, nullable=true)
     */
    private ?float $reduction_percentage;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private ?int $reduction_price_amount;

    /**
     * @ORM\Column(type="string", length=3, nullable=true, options={"collation":"utf8mb4_unicode_ci", "charset":"utf8mb4"})
     */
    private ?string $reduction_price_currency_code;

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
        ?Money $minimum_price,
        ?float $reduction_percentage,
        ?Money $reduction_price,
        bool $hidden
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->from = $from;
        $this->till = $till;
        $this->quantity = $quantity;
        $this->quantity_per_user = $quantity_per_user;
        $this->code = $code;
        $this->reduction_percentage = $reduction_percentage;
        $this->hidden = $hidden;

        if ($minimum_price !== null) {
            $this->minimum_price_amount = $minimum_price->getAmount();
            $this->minimum_price_currency_code = 'EUR';
        }

        if ($reduction_price !== null) {
            $this->reduction_price_amount = $reduction_price->getAmount();
            $this->reduction_price_currency_code = 'EUR';
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
        $cartRule->from = $dataTransferObject->from;
        $cartRule->till = $dataTransferObject->till;
        $cartRule->quantity = $dataTransferObject->quantity;
        $cartRule->quantity_per_user = $dataTransferObject->quantity_per_user;
        $cartRule->code = $dataTransferObject->code;
        $cartRule->reduction_percentage = $dataTransferObject->reduction_percentage;
        $cartRule->hidden = $dataTransferObject->hidden;

        if ($dataTransferObject->minimum_price !== null) {
            $cartRule->minimum_price_amount = $dataTransferObject->minimum_price->getAmount();
            $cartRule->minimum_price_currency_code = 'EUR';
        }

        if ($dataTransferObject->reduction_price !== null) {
            $cartRule->reduction_price_amount = $dataTransferObject->reduction_price->getAmount();
            $cartRule->reduction_price_currency_code = 'EUR';
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

    /**
     * Doctrine does not have nullable embeddables (Money type). We have to define the fields manually and turn them
     * into the embeddable object manually: https://stackoverflow.com/a/45262491/1409047
     */
    public function getMinimumPrice(): ?Money
    {
        if ($this->minimum_price_amount === null || $this->minimum_price_currency_code === null) {
            return null;
        }

        return new Money($this->minimum_price_amount, new Currency($this->minimum_price_currency_code));
    }

    public function getReductionPercentage(): ?float
    {
        return $this->reduction_percentage;
    }

    /**
     * Doctrine does not have nullable embeddables (Money type). We have to define the fields manually and turn them
     * into the embeddable object manually: https://stackoverflow.com/a/45262491/1409047
     */
    public function getReductionPrice(): ?Money
    {
        if ($this->reduction_price_amount === null || $this->reduction_price_currency_code === null) {
            return null;
        }

        return new Money($this->reduction_price_amount, new Currency($this->reduction_price_currency_code));
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
