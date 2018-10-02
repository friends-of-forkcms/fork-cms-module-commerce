<?php

namespace Backend\Modules\Catalog\Domain\UpSellProduct;

use Backend\Modules\Catalog\Domain\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_up_sell_products")
 * @ORM\Entity(repositoryClass="UpSellProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class UpSellProduct
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
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Product\Product", inversedBy="up_sell_products")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;
    
    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Catalog\Domain\Product\Product")
     * @ORM\JoinColumn(name="up_sell_product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $upSellProduct;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", length=11, nullable=true)
     */
    private $sequence;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getUpSellProduct(): ?Product
    {
        return $this->upSellProduct;
    }

    /**
     * @param Product $upSellProduct
     */
    public function setUpSellProduct(Product $upSellProduct): void
    {
        $this->upSellProduct = $upSellProduct;
    }

    /**
     * @return int
     */
    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     */
    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }
}
