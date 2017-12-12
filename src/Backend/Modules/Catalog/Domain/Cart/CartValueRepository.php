<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\Product\Product;
use Doctrine\ORM\EntityRepository;

class CartValueRepository extends EntityRepository
{
    /**
     * Get a cart value by cart and product
     *
     * @param Cart $cart
     * @param Product $product
     *
     * @return CartValue
     */
    public function getByCartAndProduct(Cart $cart, Product $product): CartValue
    {
        $query_builder = $this->createQueryBuilder('i');

        $entity = $query_builder->where('i.cart = :cart')
                                ->andWhere('i.product = :product')
                                ->setParameter('cart', $cart)
                                ->setParameter('product', $product)
                                ->getQuery()
                                ->getOneOrNullResult();

        if (!$entity) {
            $entity = new CartValue();
            $entity->setCart($cart);
            $entity->setProduct($product);
        }

        return $entity;
    }

    public function removeByIdAndCart($id, Cart $cart): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (CartValue $cartValue) {
                $this->getEntityManager()->remove($cartValue);
            },
            (array)$this->findBy(['id' => $id, 'cart' => $cart])
        );
    }
}
