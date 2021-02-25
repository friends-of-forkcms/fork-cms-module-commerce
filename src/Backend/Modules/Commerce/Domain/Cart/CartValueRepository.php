<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Modules\Commerce\Domain\Product\Product;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

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

        try {
            $entity = $query_builder->where('i.cart = :cart')
                ->andWhere('i.product = :product')
                ->setParameter('cart', $cart)
                ->setParameter('product', $product)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $entity = null;
        }

        if (!$entity) {
            $entity = new CartValue();
            $entity->setCart($cart);
            $entity->setProduct($product);
        }

        return $entity;
    }
    /**
     * Get a cart value by cart and cart value id
     *
     * @param Cart $cart
     * @param int $cartValueId
     *
     * @return CartValue|null
     */
    public function getByCartAndId(Cart $cart, int $cartValueId): ?CartValue
    {
        $query_builder = $this->createQueryBuilder('i');

        try {
            $entity = $query_builder->where('i.cart = :cart')
                ->andWhere('i.id = :id')
                ->setParameter('cart', $cart)
                ->setParameter('id', $cartValueId)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $entity = null;
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
