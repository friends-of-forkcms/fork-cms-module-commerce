<?php

namespace Backend\Modules\Commerce\Domain\Brand;

use Backend\Core\Language\Locale;
use Common\Doctrine\Entity\Meta;
use Symfony\Component\Validator\Constraints as Assert;

class BrandDataTransferObject
{
    /**
     * @var Brand
     */
    protected $brandEntity;

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
     * @var string
     */
    public $text;

    /**
     * @var Locale
     */
    public $locale;

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

    public function __construct(Brand $brand = null)
    {
        $this->brandEntity = $brand;

        if ( ! $this->hasExistingBrand()) {
            return;
        }

        $this->id      = $brand->getId();
        $this->title   = $brand->getTitle();
        $this->text    = $brand->getText();
        $this->locale  = $brand->getLocale();
        $this->meta    = $brand->getMeta();
        $this->image   = $brand->getImage();
        $this->sequence = $brand->getSequence();
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
