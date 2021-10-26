<?php

namespace Backend\Modules\Commerce\Domain\UpSellProduct;

use Backend\Modules\Commerce\Domain\Product\Product;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_up_sell_products")
 * @ORM\Entity(repositoryClass="UpSellProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class UpSellProduct
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product", inversedBy="up_sell_products")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Commerce\Domain\Product\Product")
     * @ORM\JoinColumn(name="up_sell_product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Product $upSellProduct;

    /**
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

    public function getUpSellProduct(): ?Product
    {
        return $this->upSellProduct;
    }

    public function setUpSellProduct(Product $upSellProduct): void
    {
        $this->upSellProduct = $upSellProduct;
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
