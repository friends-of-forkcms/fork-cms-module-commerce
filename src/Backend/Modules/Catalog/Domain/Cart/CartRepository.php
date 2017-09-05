<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\Cart\Exception\CartNotFound;
use Backend\Modules\ContentBlocks\Domain\ContentBlock\Exception\ContentBlockNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class CartRepository extends EntityRepository
{
    public function add(Cart $cart): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($cart);
    }

    public function findOneByIdAndLocale(?int $id, Locale $locale): ?Cart
    {
        if ($id === null) {
            throw ContentBlockNotFound::forEmptyId();
        }

        /** @var Cart $cart */
        $cart = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($cart === null) {
            throw CartNotFound::forId($id);
        }

        return $cart;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (Cart $cart) {
                $this->getEntityManager()->remove($cart);
            },
            (array) $this->findBy(['id' => $id, 'locale' => $locale])
        );
    }

    public function getStatusCount(int $status): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return $query_builder->select('COUNT(i.id) as cart_count')
                             ->andWhere('i.status = :status')
                             ->setParameter('status', $status)
                             ->getQuery()
                             ->getSingleScalarResult();
    }
}
