<?php

namespace Backend\Modules\Commerce\Domain\Brand;

use Backend\Modules\Commerce\Domain\Product\Product;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_brands")
 * @ORM\Entity(repositoryClass="BrandRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Brand
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Common\Doctrine\Entity\Meta", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="meta_id", referencedColumnName="id")
     */
    private ?Meta $meta;

    /**
     * @Gedmo\SortableGroup
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $text;

    /**
     * @ORM\Column(type="commerce_brand_image_type")
     */
    private Image $image;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private ?int $sequence;

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

    /**
     * @var Collection|Product[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", mappedBy="brand")
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    private Collection $products;

    private function __construct(
        Locale $locale,
        string $title,
        ?string $text,
        ?Image $image,
        ?int $sequence,
        Meta $meta
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->text = $text;
        $this->image = $image;
        $this->sequence = $sequence;
        $this->meta = $meta;
    }

    public static function fromDataTransferObject(BrandDataTransferObject $dataTransferObject): Brand
    {
        if ($dataTransferObject->hasExistingBrand()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(BrandDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->locale,
            $dataTransferObject->title,
            $dataTransferObject->text,
            $dataTransferObject->image,
            $dataTransferObject->sequence,
            $dataTransferObject->meta
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getImage(): Image
    {
        return $this->image;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function prepareToUploadImage(): void
    {
        $this->image->prepareToUpload();
    }

    /**
     * @ORM\PostUpdate
     * @ORM\PostPersist
     */
    public function uploadImage(): void
    {
        $this->image->upload();
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function getCreatedOn(): DateTimeInterface
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTimeInterface
    {
        return $this->editedOn;
    }

    /**
     * @ORM\PostRemove
     */
    public function postRemove(): void
    {
        $this->image->remove();
    }

    private static function update(BrandDataTransferObject $dataTransferObject): Brand
    {
        $brand = $dataTransferObject->getBrandEntity();

        $brand->locale = $dataTransferObject->locale;
        $brand->title = $dataTransferObject->title;
        $brand->text = $dataTransferObject->text;
        $brand->image = $dataTransferObject->image;
        $brand->sequence = $dataTransferObject->sequence;
        $brand->meta = $dataTransferObject->meta;

        return $brand;
    }

    public function getDataTransferObject(): BrandDataTransferObject
    {
        return new BrandDataTransferObject($this);
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function getProductCount(): int
    {
        return $this->products->count();
    }
}
