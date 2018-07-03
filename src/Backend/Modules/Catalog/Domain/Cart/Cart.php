<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Common\Core\Model;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_carts")
 * @ORM\Entity(repositoryClass="CartRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Cart
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
     * @var CartValue[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Cart\CartValue", mappedBy="cart", cascade={"remove", "persist"})
     */
    private $values;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $total_quantity = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $session_id;

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
     * @var float
     */
    private $total;

    /**
     * @var float
     */
    private $subTotal;

    /**
     * @var array
     */
    private $vats = [];

    /**
     * @var bool
     */
    private $allProductsInStock;

    /**
     * @var double
     */
    private $totalWeight;

    /**
     * Cart constructor.
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
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
     * @return CartValue[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param CartValue $value
     *
     * @return void
     */
    public function addValue(CartValue $value): void
    {
        $this->values->add($value);

        // Recalculate on add
        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    /**
     * @param CartValue $value
     *
     * @return void
     */
    public function removeValue(CartValue $value): void
    {
        $this->values->removeElement($value);

        $this->calculateTotalQuantity();
        $this->calculateTotals();
    }

    /**
     * @return int
     */
    public function getTotalQuantity(): int
    {
        return $this->total_quantity;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->session_id;
    }

    /**
     * @param string $session_id
     */
    public function setSessionId(string $session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * @return DateTime
     */
    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    /**
     * @param DateTime $createdOn
     */
    public function setCreatedOn(DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return DateTime
     */
    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    /**
     * @param DateTime $editedOn
     */
    public function setEditedOn(DateTime $editedOn)
    {
        $this->editedOn = $editedOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->editedOn = new DateTime();

        if (!$this->id) {
            $this->createdOn = $this->editedOn;
        }
    }

    /**
     * Get the total cart value
     *
     * @return float
     */
    public function getTotal(): float
    {
        if (!$this->total) {
            $this->calculateTotals();
        }

        return $this->total;
    }

    /**
     * @return float
     */
    public function getSubTotal(): float
    {
        if (!$this->subTotal) {
            $this->calculateTotals();
        }

        return $this->subTotal;
    }

    /**
     * @return array
     */
    public function getVats(): array
    {
        if (!$this->vats) {
            $this->calculateTotals();
        }

        return $this->vats;
    }

    /**
     * @return float|null
     */
    public function getTotalWeight(): float
    {
        if ($this->totalWeight === null) {
            $this->calculateTotals();
        }

        return $this->totalWeight;
    }

    /**
     * Calculate the cart totals
     *
     * @return void
     */
    public function calculateTotals(): void
    {
        // Reset the values
        $this->subTotal = 0;
        $this->total = 0;
        $this->vats = [];
        $this->totalWeight = 0;

        // Store new values
        foreach ($this->values as $value) {
            // Update the product totals
            $product = $value->getProduct();
            $vat = $product->getVat();
            $vatPrice = $product->getVatPrice() * $value->getQuantity();

            if ($product->getWeight() !== null) {
                $this->totalWeight += $product->getWeight() * $value->getQuantity();
            }

            $this->subTotal += $value->getTotal();

            if (!array_key_exists($vat->getId(), $this->vats)) {
                $this->vats[$vat->getId()] = [
                    'title' => $vat->getTitle(),
                    'total' => 0
                ];
            }

            $this->vats[$vat->getId()]['total'] += $vatPrice;
            $this->total += $value->getTotal() + $vatPrice;

            // Update the product option totals
            foreach ($value->getCartValueOptions() as $valueOption) {
                $productOptionValue = $valueOption->getProductOptionValue();
                $vat = $productOptionValue->getVat();

                if (!array_key_exists($vat->getId(), $this->vats)) {
                    $this->vats[$vat->getId()] = [
                        'title' => $vat->getTitle(),
                        'total' => 0
                    ];
                }

                $vatPrice = $productOptionValue->getVatPrice() * $value->getQuantity();
                $productOptionValueTotal = $valueOption->getTotal();

                $this->subTotal += $productOptionValueTotal;
                $this->vats[$vat->getId()]['total'] += $vatPrice;
                $this->total += $productOptionValueTotal + $vatPrice;
            }
        }
    }

    /**
     * Calculate the total quantity
     *
     * @return void
     */
    private function calculateTotalQuantity(): void
    {
        $totalQuantity = 0;

        foreach ($this->values as $value) {
            $totalQuantity += $value->getQuantity();
        }

        $this->total_quantity = $totalQuantity;
    }

    /**
     * Check if all the products are in stock
     *
     * @return boolean
     */
    public function isProductsInStock(): bool
    {
        if ($this->allProductsInStock === null) {
            $inStock = true;

            foreach ($this->getValues() as $value) {
                if (!$value->isInStock()) {
                    $inStock = false;
                }
            }

            $this->allProductsInStock = $inStock;
        }

        return $this->allProductsInStock;
    }
}
