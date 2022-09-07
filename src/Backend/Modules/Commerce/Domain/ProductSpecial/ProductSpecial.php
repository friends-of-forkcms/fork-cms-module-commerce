<?php

namespace Backend\Modules\Commerce\Domain\ProductSpecial;

use Backend\Modules\Commerce\Domain\Product\Product;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="commerce_product_specials")
 * @ORM\Entity(repositoryClass="ProductRepository")
 */
class ProductSpecial
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="specials")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @ORM\Embedded(class="\Money\Money", columnPrefix="price")
     */
    private Money $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $endDate;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $updatedAt;

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
    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): void
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
            if ($this->startDate > $this->endDate) {
                $context->buildViolation('err.EndDateAfterStartDate')
                        ->atPath('endDate')
                        ->addViolation();
            }
        }
    }
}
