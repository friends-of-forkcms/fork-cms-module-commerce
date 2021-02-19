<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class CartValueOptionRepository extends EntityRepository
{
    /**
     * Get a cart value by cart and product
     *
     * @param CartValue $cartValue
     * @param ProductOptionValue $productOptionValue
     *
     * @throws NonUniqueResultException
     *
     * @return CartValueOption|null
     */
    public function getByCartValueAndProductOptionValue(CartValue $cartValue, ProductOptionValue $productOptionValue): ?CartValueOption
    {
        $query_builder = $this->createQueryBuilder('i');
        $entity = null;

        if ($cartValue->getId() && $productOptionValue->getId()) {
            $entity = $query_builder->where('i.cart_value = :cart_value')
                ->andWhere('i.product_option_value = :product_option_value')
                ->setParameter('cart_value', $cartValue)
                ->setParameter('product_option_value', $productOptionValue)
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $entity;
    }
}
