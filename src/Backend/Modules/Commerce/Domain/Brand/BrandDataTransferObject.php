<?php

namespace Backend\Modules\Commerce\Domain\Brand;

use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class BrandDataTransferObject
{
    protected ?Brand $brandEntity = null;
    public ?int $id = null;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $title;
    public ?string $text = null;
    public Locale $locale;
    public ?Meta $meta = null;
    public Image $image;
    public int $sequence;

    public function __construct(Brand $brand = null)
    {
        $this->brandEntity = $brand;

        if (!$this->hasExistingBrand()) {
            return;
        }

        $this->id = $this->brandEntity->getId();
        $this->title = $this->brandEntity->getTitle();
        $this->text = $this->brandEntity->getText();
        $this->locale = $this->brandEntity->getLocale();
        $this->meta = $this->brandEntity->getMeta();
        $this->image = $this->brandEntity->getImage();
        $this->sequence = $this->brandEntity->getSequence();
    }

    public function getBrandEntity(): Brand
    {
        return $this->brandEntity;
    }

    public function hasExistingBrand(): bool
    {
        return $this->brandEntity instanceof Brand;
    }
}
