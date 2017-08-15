<?php

namespace Backend\Modules\Catalog\Domain\StockStatus;

use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_stock_statuses")
 * @ORM\Entity(repositoryClass="StockStatusRepository")
 * @ORM\HasLifecycleCallbacks
 */
class StockStatus
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
        $this->locale     = $locale;
        $this->title      = $title;
    }

    public static function fromDataTransferObject(StockStatusDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingStockStatus()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(StockStatusDataTransferObject $dataTransferObject): self
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

    public function setSequence($sequence): void
    {
        $this->sequence = $sequence;
    }

    private static function update(StockStatusDataTransferObject $dataTransferObject)
    {
        $stockStatus = $dataTransferObject->getStockStatusEntity();

        $stockStatus->locale     = $dataTransferObject->locale;
        $stockStatus->title      = $dataTransferObject->title;

        return $stockStatus;
    }

    public function getDataTransferObject(): StockStatusDataTransferObject
    {
        return new StockStatusDataTransferObject($this);
    }
}
