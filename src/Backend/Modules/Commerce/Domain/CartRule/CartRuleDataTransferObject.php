<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Backend\Core\Language\Locale;
use DateTime;
use DateTimeInterface;
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
    public string $code;

    public ?float $minimum_amount = null;

    public ?float $reduction_percentage = null;

    public ?float $reduction_amount = null;

    public bool $hidden;

    public Locale $locale;

    public function __construct(CartRule $cartRule = null)
    {
        $this->cartRuleEntity = $cartRule;
        $this->locale = Locale::workingLocale();
        $this->hidden = false;
        $this->from = new DateTime();
        $this->quantity = 0;

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
        $this->minimum_amount = $this->cartRuleEntity->getMinimumAmount();
        $this->reduction_percentage = $this->cartRuleEntity->getReductionPercentage();
        $this->reduction_amount = $this->cartRuleEntity->getReductionAmount();
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
