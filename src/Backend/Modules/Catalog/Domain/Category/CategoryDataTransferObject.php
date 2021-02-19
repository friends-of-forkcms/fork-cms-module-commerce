<?php

namespace Backend\Modules\Catalog\Domain\Category;

use Backend\Core\Language\Locale;
use Common\Doctrine\Entity\Meta;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryDataTransferObject
{
    /**
     * @var Category
     */
    protected $categoryEntity;

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
    public $intro;

    /**
     * @var string
     */
    public $text;

    /**
     * @var Locale
     */
    public $locale;

    /**
     * @var Category
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
    public $googleTaxonomyId;

    /**
     * @var int
     */
    public $sequence;

    public function __construct(Category $category = null)
    {
        $this->categoryEntity = $category;

        if (!$this->hasExistingCategory()) {
            return;
        }

        $this->id = $category->getId();
        $this->extraId = $category->getExtraId();
        $this->googleTaxonomyId = $category->getGoogleTaxonomyId();
        $this->title = $category->getTitle();
        $this->intro = $category->getIntro();
        $this->text = $category->getText();
        $this->locale = $category->getLocale();
        $this->parent = $category->getParent();
        $this->meta = $category->getMeta();
        $this->image = $category->getImage();
        $this->sequence = $category->getSequence();
    }

    public function getCategoryEntity(): Category
    {
        return $this->categoryEntity;
    }

    public function hasExistingCategory(): bool
    {
        return $this->categoryEntity instanceof Category;
    }
}
