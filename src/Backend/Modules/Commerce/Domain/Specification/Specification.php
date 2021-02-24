<?php

namespace Backend\Modules\Commerce\Domain\Specification;

use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_specifications")
 * @ORM\Entity(repositoryClass="SpecificationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Specification
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
     * @var Collection|SpecificationValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue", mappedBy="specification", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sequence": "ASC"})
     */
    private Collection $specification_values;

    /**
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="integer", length=11)
     */
    private int $sequence;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $filter;

    public const TYPE_TEXTBOX = 1;

    private function __construct(
        Locale $locale,
        string $title,
        int $sequence,
        Meta $meta,
        bool $filter,
        $specification_values
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->sequence = $sequence;
        $this->meta = $meta;
        $this->filter = $filter;
        $this->specification_values = $specification_values;
    }

    public static function fromDataTransferObject(SpecificationDataTransferObject $dataTransferObject): Specification
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
            $dataTransferObject->filter,
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

    public function isFilter(): bool
    {
        return $this->filter;
    }

    /**
     * @return Collection|SpecificationValue[]
     */
    public function getSpecificationValues(): Collection
    {
        return $this->specification_values;
    }

    private static function update(SpecificationDataTransferObject $dataTransferObject): Specification
    {
        $specification = $dataTransferObject->getSpecificationEntity();

        $specification->locale = $dataTransferObject->locale;
        $specification->title = $dataTransferObject->title;
        $specification->sequence = $dataTransferObject->sequence;
        $specification->meta = $dataTransferObject->meta;
        $specification->filter = $dataTransferObject->filter;
        $specification->specification_values = $dataTransferObject->specification_values;

        return $specification;
    }

    public function getDataTransferObject(): SpecificationDataTransferObject
    {
        return new SpecificationDataTransferObject($this);
    }
}
