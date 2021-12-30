<?php

namespace Backend\Modules\Commerce\Domain\UpSellProduct;

use Backend\Modules\Commerce\Domain\Product\Product;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_up_sell_products")
 * @ORM\Entity(repositoryClass="UpSellProductRepository")
 */
class UpSellProduct
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="upsellProducts")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product")
     * @ORM\JoinColumn(name="upsellProductId", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $upsellProduct;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private ?int $sequence;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function getUpsellProduct(): ?Product
    {
        return $this->upsellProduct;
    }

    public function setUpsellProduct(Product $upsellProduct): void
    {
        $this->upsellProduct = $upsellProduct;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(?int $sequence): void
    {
        $this->sequence = $sequence;
    }
}
