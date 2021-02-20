<?php

namespace Backend\Modules\Catalog\Domain\Category;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Modules\Catalog\Domain\Product\Product;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Frontend\Core\Engine\Navigation;

/**
 * @ORM\Table(name="catalog_categories")
 * @ORM\Entity(repositoryClass="CategoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Category
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
     * @var Meta
     *
     * @ORM\ManyToOne(targetEntity="Common\Doctrine\Entity\Meta",cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="meta_id", referencedColumnName="id")
     */
    private $meta;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="extra_id")
     */
    private $extraId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="google_taxonomy_id", nullable=true)
     */
    private $googleTaxonomyId;

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

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $intro;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @var Image
     *
     * @ORM\Column(type="catalog_category_image_type")
     */
    private $image;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private $sequence;

    /**
     * @var Category[]
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    private $children;

    /**
     * @var Product[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Product\Product", mappedBy="category")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    private $products;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="created_on")
     */
    private $createdOn;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="edited_on")
     */
    private $editedOn;

    /**
     * This is used to determine the path of our children for a display
     *
     * @var int
     */
    public $path;

    /**
     * This is used to store the url path
     *
     * @var string
     */
    private $urlPrefix;

    private function __construct(
        int $extraId,
        ?int $googleTaxonomyId,
        Locale $locale,
        string $title,
        ?string $intro,
        ?string $text,
        ?Image $image,
        int $sequence,
        Meta $meta,
        ?Category $parent
    )
    {
        $this->extraId = $extraId;
        $this->googleTaxonomyId = $googleTaxonomyId;
        $this->locale = $locale;
        $this->title = $title;
        $this->intro = $intro;
        $this->text = $text;
        $this->image = $image;
        $this->sequence = $sequence;
        $this->meta = $meta;
        $this->parent = $parent;
    }

    public static function fromDataTransferObject(CategoryDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingCategory()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(CategoryDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->extraId,
            $dataTransferObject->googleTaxonomyId,
            $dataTransferObject->locale,
            $dataTransferObject->title,
            $dataTransferObject->intro,
            $dataTransferObject->text,
            $dataTransferObject->image,
            $dataTransferObject->sequence,
            $dataTransferObject->meta,
            $dataTransferObject->parent
        );
    }

    private static function update(CategoryDataTransferObject $dataTransferObject)
    {
        $category = $dataTransferObject->getCategoryEntity();

        $category->extraId = $dataTransferObject->extraId;
        $category->googleTaxonomyId = $dataTransferObject->googleTaxonomyId;
        $category->locale = $dataTransferObject->locale;
        $category->title = $dataTransferObject->title;
        $category->intro = $dataTransferObject->intro;
        $category->text = $dataTransferObject->text;
        $category->image = $dataTransferObject->image;
        $category->sequence = $dataTransferObject->sequence;
        $category->meta = $dataTransferObject->meta;
        $category->parent = $dataTransferObject->parent;

        return $category;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getExtraId(): int
    {
        return $this->extraId;
    }

    public function getGoogleTaxonomyId(): ?int
    {
        return $this->googleTaxonomyId;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getImage(): Image
    {
        return $this->image;
    }

    /**
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     */
    public function prepareToUploadImage()
    {
        $this->image->prepareToUpload();
    }

    /**
     * @ORM\PostUpdate()
     * @ORM\PostPersist()
     */
    public function uploadImage()
    {
        $this->image->upload();
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence($sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    /**
     * @return Category[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdOn = $this->editedOn = new DateTime();
    }

    /**
     * @ORM\PostPersist
     */
    public function postPersist()
    {
        $this->updateWidget();
    }

    /**
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        $this->image->remove();
    }

    /**
     * Update the widget so it shows the correct title and has the correct template
     */
    private function updateWidget()
    {
        $editUrl = Model::createUrlForAction('EditCategory', 'Catalog', (string)$this->locale) . '&id=' . $this->id;

        // update data for the extra
        // @TODO replace this with an implementation with doctrine
        $extras = Model::getExtras([$this->extraId]);
        $extra = reset($extras);
        $data = [
            'id' => $this->id,
            'language' => (string)$this->locale,
            'edit_url' => $editUrl,
        ];
        if (isset($extra['data'])) {
            $data = $data + (array)$extra['data'];
        }
        $data['extra_label'] = ucfirst(Language::lbl('Category')) . ' - ' . $this->title;

        Model::updateExtra($this->extraId, 'data', $data);
    }

    public function getDataTransferObject(): CategoryDataTransferObject
    {
        return new CategoryDataTransferObject($this);
    }

    /**
     * Get the frontend url based on module, meta and parent category
     */
    public function getUrl(): string
    {
        if (!$this->urlPrefix) {
            if ($this->parent) {
                $this->urlPrefix = $this->parent->getUrl();
            } else {
                $this->urlPrefix = Navigation::getUrlForBlock('Catalog', 'Index', $this->locale->getLocale());
            }
        }

        return $this->urlPrefix . '/' . $this->meta->getUrl();
    }

    public function getActiveProducts()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create()->where($expr->eq('hidden', false));

        return $this->products->matching($criteria);
    }
}
