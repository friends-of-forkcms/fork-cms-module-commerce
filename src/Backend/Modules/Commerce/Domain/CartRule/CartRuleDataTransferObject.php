<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Backend\Core\Language\Locale;
use DateTime;
use DateTimeInterface;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;

class CartRuleDataTransferObject
{
    protected ?CartRule $cartRuleEntity = null;
    public int $id;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $title;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public DateTimeInterface $from;

    public ?DateTimeInterface $till = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public int $quantity;

    public ?int $quantity_per_user = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"Edit"})
     */
    public ?string $code;

    public ?Money $minimum_price = null;

    public ?float $reduction_percentage = null;
    public ?Money $reduction_price = null;

    public bool $hidden;

    public Locale $locale;

    public function __construct(CartRule $cartRule = null)
    {
        $this->cartRuleEntity = $cartRule;
        $this->locale = Locale::workingLocale();
        $this->hidden = false;
        $this->from = new DateTime();
        $this->quantity = 0;
        $this->minimum_price = Money::EUR(0);
        $this->reduction_price = Money::EUR(0);
        $this->reduction_percentage = 0;

        if (!$this->hasExistingCartRule()) {
            return;
        }

        $this->id = $this->cartRuleEntity->getId();
        $this->locale = $this->cartRuleEntity->getLocale();
        $this->title = $this->cartRuleEntity->getTitle();
        $this->from = $this->cartRuleEntity->getFrom();
        $this->till = $this->cartRuleEntity->getTill();
        $this->quantity = $this->cartRuleEntity->getQuantity();
        $this->quantity_per_user = $this->cartRuleEntity->getQuantityPerUser();
        $this->code = $this->cartRuleEntity->getCode();
        $this->minimum_price = $this->cartRuleEntity->getMinimumPrice();
        $this->reduction_percentage = $this->cartRuleEntity->getReductionPercentage();
        $this->reduction_price = $this->cartRuleEntity->getReductionPrice();
        $this->hidden = $this->cartRuleEntity->isHidden();
    }

    public function setCartRuleEntity(CartRule $cartRule): void
    {
        $this->cartRuleEntity = $cartRule;
    }

    public function getCartRuleEntity(): CartRule
    {
        return $this->cartRuleEntity;
    }

    public function hasExistingCartRule(): bool
    {
        return $this->cartRuleEntity instanceof CartRule;
    }
}
