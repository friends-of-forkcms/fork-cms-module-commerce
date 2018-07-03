<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus;

use Backend\Core\Language\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class OrderStatusDataTransferObject
{
    /**
     * @var OrderStatus
     */
    protected $orderStatusEntity;

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
     * @var Locale
     */
    public $locale;

    /**
     * @var int
     */
    public $type;

    public function __construct(OrderStatus $orderStatus = null)
    {
        $this->orderStatusEntity = $orderStatus;

        if ( ! $this->hasExistingOrderStatus()) {
            return;
        }

        $this->id       = $orderStatus->getId();
        $this->title    = $orderStatus->getTitle();
        $this->locale   = $orderStatus->getLocale();
    }

    public function getOrderStatusEntity(): OrderStatus
    {
        return $this->orderStatusEntity;
    }

    public function hasExistingOrderStatus(): bool
    {
        return $this->orderStatusEntity instanceof OrderStatus;
    }
}
