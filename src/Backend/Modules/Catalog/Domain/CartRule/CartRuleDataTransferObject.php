<?php

namespace Backend\Modules\Catalog\Domain\CartRule;

use Backend\Core\Language\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class CartRuleDataTransferObject
{
    /**
     * @var CartRule
     */
    protected $cartRuleEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $from;

    /**
     * @var \DateTime
     */
    public $till;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $quantity;

    /**
     * @var int
     */
    public $quantity_per_user;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired", groups={"Edit"})
     */
    public $code;

    /**
     * @var float
     */
    public $minimum_amount;

    /**
     * @var float
     */
    public $reduction_percentage;

    /**
     * @var float
     */
    public $reduction_amount;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var Locale
     */
    public $locale;

    public function __construct(CartRule $cartRule = null)
    {
        $this->cartRuleEntity = $cartRule;
        $this->locale = Locale::workingLocale();
        $this->hidden = false;
        $this->from = new \DateTime();
        $this->quantity = 0;

        if (!$this->hasExistingCartRule()) {
            return;
        }

        $this->id = $cartRule->getId();
        $this->locale = $cartRule->getLocale();
        $this->title = $cartRule->getTitle();
        $this->from = $cartRule->getFrom();
        $this->till = $cartRule->getTill();
        $this->quantity = $cartRule->getQuantity();
        $this->quantity_per_user = $cartRule->getQuantityPerUser();
        $this->code = $cartRule->getCode();
        $this->minimum_amount = $cartRule->getMinimumAmount();
        $this->reduction_percentage = $cartRule->getReductionPercentage();
        $this->reduction_amount = $cartRule->getReductionAmount();
        $this->hidden = $cartRule->isHidden();
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
