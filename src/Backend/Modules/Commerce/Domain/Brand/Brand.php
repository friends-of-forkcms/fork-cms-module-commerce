<?php

namespace Backend\Modules\Commerce\Domain\Brand;

use Backend\Modules\Commerce\Domain\Product\Product;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_brands")
 * @ORM\Entity(repositoryClass="BrandRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Brand
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
    private $text;

    /**
     * @var Image
     *
     * @ORM\Column(type="commerce_brand_image_type")
     */
    private $image;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private $sequence;

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
     * @var Product[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", mappedBy="brand")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    private $products;

    private function __construct(
        Locale $locale,
        string $title,
        ?string $text,
        ?Image $image,
        int $sequence,
        Meta $meta
    ) {
        $this->locale   = $locale;
        $this->title    = $title;
        $this->text     = $text;
        $this->image    = $image;
        $this->sequence = $sequence;
        $this->meta     = $meta;
    }

    public static function fromDataTransferObject(BrandDataTransferObject $dataTransferObject)
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
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        $this->image->remove();
    }

    private static function update(BrandDataTransferObject $dataTransferObject)
    {
        $brand = $dataTransferObject->getBrandEntity();

        $brand->locale   = $dataTransferObject->locale;
        $brand->title    = $dataTransferObject->title;
        $brand->text     = $dataTransferObject->text;
        $brand->image    = $dataTransferObject->image;
        $brand->sequence = $dataTransferObject->sequence;
        $brand->meta     = $dataTransferObject->meta;

        return $brand;
    }

    public function getDataTransferObject(): BrandDataTransferObject
    {
        return new BrandDataTransferObject($this);
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function getProductCount(): int
    {
        return $this->products->count();
    }
}
