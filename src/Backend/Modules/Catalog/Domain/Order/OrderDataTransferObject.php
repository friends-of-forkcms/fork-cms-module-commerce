<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Core\Language\Locale;
use Common\Doctrine\Entity\Meta;
use Symfony\Component\Validator\Constraints as Assert;

class OrderDataTransferObject
{
    /**
     * @var Order
     */
    protected $orderEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $extraId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var string
     */
    public $text;

    /**
     * @var Locale
     */
    public $locale;

    /**
     * @var Order
     */
    public $parent;

    /**
     * @var Meta
     */
    public $meta;

    /**
     * @var Image
     */
    public $image;

    /**
     * @var int
     */
    public $sequence;

    public function __construct(Order $order = null)
    {
        $this->orderEntity = $order;

        if ( ! $this->hasExistingOrder()) {
            return;
        }

        $this->id      = $order->getId();
        $this->extraId = $order->getExtraId();
        $this->title   = $order->getTitle();
        $this->text    = $order->getText();
        $this->locale  = $order->getLocale();
        $this->parent  = $order->getParent();
        $this->meta    = $order->getMeta();
        $this->image   = $order->getImage();
        $this->sequence = $order->getSequence();
    }

    public function getOrderEntity(): Order
    {
        return $this->orderEntity;
    }

    public function hasExistingOrder(): bool
    {
        return $this->orderEntity instanceof Order;
    }
}
