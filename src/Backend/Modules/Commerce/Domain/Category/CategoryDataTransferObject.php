<?php

namespace Backend\Modules\Commerce\Domain\Category;

use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryDataTransferObject
{
    protected ?Category $categoryEntity = null;
    public int $id;
    public int $extraId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $title;
    public ?string $intro = null;
    public ?string $text = null;
    public Locale $locale;
    public ?Category $parent = null;
    public ?Meta $meta = null;
    public Image $image;
    public ?int $googleTaxonomyId = null;
    public ?int $sequence;

    public function __construct(Category $category = null)
    {
        $this->categoryEntity = $category;
        $this->sequence = null;

        if (!$this->hasExistingCategory()) {
            return;
        }

        $this->id = $this->categoryEntity->getId();
        $this->extraId = $this->categoryEntity->getExtraId();
        $this->googleTaxonomyId = $this->categoryEntity->getGoogleTaxonomyId();
        $this->title = $this->categoryEntity->getTitle();
        $this->intro = $this->categoryEntity->getIntro();
        $this->text = $this->categoryEntity->getText();
        $this->locale = $this->categoryEntity->getLocale();
        $this->parent = $this->categoryEntity->getParent();
        $this->meta = $this->categoryEntity->getMeta();
        $this->image = $this->categoryEntity->getImage();
        $this->sequence = $this->categoryEntity->getSequence();
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
