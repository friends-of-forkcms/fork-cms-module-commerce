<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus;

use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_order_statuses")
 * @ORM\Entity(repositoryClass="OrderStatusRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderStatus
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

    private function __construct(
        Locale $locale,
        string $title
    ) {
        $this->locale = $locale;
        $this->title  = $title;
    }

    public static function fromDataTransferObject(OrderStatusDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingOrderStatus()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderStatusDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->locale,
            $dataTransferObject->title
        );
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

    private static function update(OrderStatusDataTransferObject $dataTransferObject)
    {
        $orderStatus = $dataTransferObject->getOrderStatusEntity();

        $orderStatus->locale = $dataTransferObject->locale;
        $orderStatus->title  = $dataTransferObject->title;

        return $orderStatus;
    }

    public function getDataTransferObject(): OrderStatusDataTransferObject
    {
        return new OrderStatusDataTransferObject($this);
    }
}
