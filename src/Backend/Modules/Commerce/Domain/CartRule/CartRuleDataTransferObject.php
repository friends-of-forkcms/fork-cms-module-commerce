<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Backend\Core\Language\Locale;
use DateTime;
use DateTimeInterface;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
    public DateTimeInterface $fromDate;

    public ?DateTimeInterface $tillDate = null;

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
        $this->fromDate = new DateTime();
        $this->quantity = 0;
        $this->minimum_price = Money::EUR(0);

        if (!$this->hasExistingCartRule()) {
            return;
        }

        $this->id = $this->cartRuleEntity->getId();
        $this->locale = $this->cartRuleEntity->getLocale();
        $this->title = $this->cartRuleEntity->getTitle();
        $this->fromDate = $this->cartRuleEntity->getFromDate();
        $this->tillDate = $this->cartRuleEntity->getTillDate();
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

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context): void
    {
        // Validate dates
        if (($this->fromDate && $this->tillDate) && ($this->fromDate > $this->tillDate)) {
            $context->buildViolation('err.EndDateAfterStartDate')
                ->atPath('tillDate')
                ->addViolation();
        }

        // Validate reduction discount
        $isEmptyReductionPercentage = empty($this->reduction_percentage);
        $isEmptyReductionPrice = $this->reduction_price === null || $this->reduction_price->isZero();

        $isBothEmptyOrBothFilledIn = ($isEmptyReductionPercentage && $isEmptyReductionPrice)
            || (!$isEmptyReductionPercentage && !$isEmptyReductionPrice);

        if ($isBothEmptyOrBothFilledIn) {
            $context->buildViolation('err.ValidateOneOfReductionDiscountsNeeded')
                ->atPath('reduction_percentage')
                ->addViolation();
        }
    }
}
