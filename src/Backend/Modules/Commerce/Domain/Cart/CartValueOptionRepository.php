<?php

namespace Backend\Modules\Commerce\Domain\Cart;

use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class CartValueOptionRepository extends EntityRepository
{
    /**
     * Get a cart value by cart and product.
     *
     * @throws NonUniqueResultException
     */
    public function getByCartValueAndProductOptionValue(CartValue $cartValue, ProductOptionValue $productOptionValue): ?CartValueOption
    {
        $query_builder = $this->createQueryBuilder('i');
        $entity = null;

        if ($cartValue->getId() && $productOptionValue->getId()) {
            $entity = $query_builder->where('i.cart_value = :cart_value')
                ->andWhere('i.productOptionValue = :product_option_value')
                ->setParameter('cart_value', $cartValue)
                ->setParameter('product_option_value', $productOptionValue)
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $entity;
    }
}
