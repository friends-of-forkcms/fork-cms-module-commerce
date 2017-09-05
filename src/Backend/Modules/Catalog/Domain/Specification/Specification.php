<?php

namespace Backend\Modules\Catalog\Domain\Specification;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_specifications")
 * @ORM\Entity(repositoryClass="SpecificationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Specification
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
     * @var SpecificationValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\SpecificationValue\SpecificationValue", mappedBy="specification", cascade={"remove", "persist"})
     */
    private $specification_values;

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
     * @ORM\Column(type="integer", length=11)
     */
    private $sequence;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", length=11)
     */
    private $type;

    public const TYPE_TEXTBOX = 1;

    private function __construct(
        Locale $locale,
        string $title,
        int $sequence,
        Meta $meta,
        int $type,
        $specification_values
    ) {
        $this->locale               = $locale;
        $this->title                = $title;
        $this->sequence             = $sequence;
        $this->meta                 = $meta;
        $this->type                 = $type;
        $this->specification_values = $specification_values;
    }

    public static function fromDataTransferObject(SpecificationDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingSpecification()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(SpecificationDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->locale,
            $dataTransferObject->title,
            $dataTransferObject->sequence,
            $dataTransferObject->meta,
            $dataTransferObject->type,
            $dataTransferObject->specification_values
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

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence($sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return SpecificationValue[]
     */
    public function getSpecificationValues()
    {
        return $this->specification_values;
    }

    private static function update(SpecificationDataTransferObject $dataTransferObject)
    {
        $specification = $dataTransferObject->getSpecificationEntity();

        $specification->locale               = $dataTransferObject->locale;
        $specification->title                = $dataTransferObject->title;
        $specification->sequence             = $dataTransferObject->sequence;
        $specification->meta                 = $dataTransferObject->meta;
        $specification->type                 = $dataTransferObject->type;
        $specification->specification_values = $dataTransferObject->specification_values;

        return $specification;
    }

    public function getDataTransferObject(): SpecificationDataTransferObject
    {
        return new SpecificationDataTransferObject($this);
    }
}
