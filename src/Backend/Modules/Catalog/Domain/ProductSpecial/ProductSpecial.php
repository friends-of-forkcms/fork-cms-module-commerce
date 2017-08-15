<?php

namespace Backend\Modules\Catalog\Domain\ProductSpecial;

use DateTime;
use Backend\Modules\Catalog\Domain\Product\Product;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="catalog_product_specials")
 * @ORM\Entity(repositoryClass="ProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductSpecial
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
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Product\Product", inversedBy="specials")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;

    /**
     * @var float
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="start_date")
     */
    private $startDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="end_date", nullable=true)
     */
    private $endDate;

    public function __construct()
    {
        $this->startDate = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     *
     * @return float
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price)
    {
        $this->price = $price;
    }

    /**
     * @Assert\Date(message="err.InvalidDate")
     *
     * @return DateTime
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     */
    public function setStartDate(DateTime $startDate)
    {
        $startDate->setTime(0, 0, 0);

        $this->startDate = $startDate;
    }

    /**
     * @Assert\Date(message="err.InvalidDate")
     * @Assert\Date(message="err.InvalidDate")
     *
     * @return DateTime
     */
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     */
    public function setEndDate(?DateTime $endDate)
    {
        if ($endDate) {
            $endDate->setTime(0, 0, 0);
        }

        $this->endDate = $endDate;
    }

    /**
     * @Assert\Callback
     *
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function isDateValid(ExecutionContextInterface $context, $payload)
    {
        if ($this->endDate && $this->startDate) {
            $difference = $this->endDate->diff($this->startDate);

            if ($difference->invert == 0 && $difference->days > 0) {
                $context->buildViolation('err.EndDateAfterStartDate')
                        ->atPath('end_date')
                        ->addViolation();
            }
        }
    }
}
