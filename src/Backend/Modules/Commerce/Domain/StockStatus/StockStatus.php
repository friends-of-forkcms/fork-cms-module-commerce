<?php

namespace Backend\Modules\Commerce\Domain\StockStatus;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_stock_statuses")
 * @ORM\Entity(repositoryClass="StockStatusRepository")
 * @ORM\HasLifecycleCallbacks
 */
class StockStatus
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
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdOn;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="edited_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $editedOn;

    private function __construct(Locale $locale, string $title)
    {
        $this->locale = $locale;
        $this->title = $title;
    }

    public static function fromDataTransferObject(StockStatusDataTransferObject $dataTransferObject): StockStatus
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

    private static function update(StockStatusDataTransferObject $dataTransferObject): StockStatus
    {
        $stockStatus = $dataTransferObject->getStockStatusEntity();

        $stockStatus->locale = $dataTransferObject->locale;
        $stockStatus->title = $dataTransferObject->title;

        return $stockStatus;
    }

    public function getDataTransferObject(): StockStatusDataTransferObject
    {
        return new StockStatusDataTransferObject($this);
    }
}
