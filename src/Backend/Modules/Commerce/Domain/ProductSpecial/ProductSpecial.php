<?php

namespace Backend\Modules\Commerce\Domain\ProductSpecial;

use Backend\Modules\Commerce\Domain\Product\Product;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="commerce_product_specials")
 * @ORM\Entity(repositoryClass="ProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductSpecial
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="specials")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $price;

    /**
     * @ORM\Column(type="datetime", name="start_date")
     */
    private DateTimeInterface $startDate;

    /**
     * @ORM\Column(type="datetime", name="end_date", nullable=true)
     */
    private ?DateTimeInterface $endDate;

    public function __construct()
    {
        $this->startDate = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @Assert\Date(message="err.InvalidDate")
     */
    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): void
    {
        $startDate->setTime(0, 0);

        $this->startDate = $startDate;
    }

    /**
     * @Assert\Date(message="err.InvalidDate")
     * @Assert\Date(message="err.InvalidDate")
     */
    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeInterface $endDate): void
    {
        if ($endDate) {
            $endDate->setTime(0, 0);
        }

        $this->endDate = $endDate;
    }

    /**
     * @Assert\Callback
     *
     * @param $payload
     */
    public function isDateValid(ExecutionContextInterface $context, $payload): void
    {
        if ($this->endDate && $this->startDate) {
            $difference = $this->endDate->diff($this->startDate);

            if ($difference->invert === 0 && $difference->days > 0) {
                $context->buildViolation('err.EndDateAfterStartDate')
                        ->atPath('end_date')
                        ->addViolation();
            }
        }
    }
}
