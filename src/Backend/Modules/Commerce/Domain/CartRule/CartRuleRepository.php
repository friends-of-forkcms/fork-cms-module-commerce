<?php

namespace Backend\Modules\Commerce\Domain\CartRule;

use Backend\Modules\Commerce\Domain\CartRule\Exception\CartRuleNotFound;
use Common\Locale;
use DateTime;
use Doctrine\ORM\EntityRepository;

class CartRuleRepository extends EntityRepository
{
    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function add(CartRule $cartRule): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($cartRule);
    }

    /**
     * @throws CartRuleNotFound
     */
    public function findOneByIdAndLocale(?int $id, Locale $locale): ?CartRule
    {
        if ($id === null) {
            throw CartRuleNotFound::forEmptyId();
        }

        /** @var CartRule $cartRule */
        $cartRule = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($cartRule === null) {
            throw CartRuleNotFound::forId($id);
        }

        return $cartRule;
    }

    /**
     * @throws CartRuleNotFound
     */
    public function findOneById(int $id): CartRule
    {
        /** @var CartRule $cartRule */
        $cartRule = $this->findOneBy(['id' => $id]);

        if ($cartRule === null) {
            throw CartRuleNotFound::forId($id);
        }

        return $cartRule;
    }

    public function remove(CartRule $cartRule): void
    {
        $this->getEntityManager()->remove($cartRule);
    }

    public function findByCode(string $code): ?CartRule
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->where('i.code LIKE :code')
            ->setParameters([
                'code' => $code,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findValidByCode(string $code): ?CartRule
    {
        $queryBuilder = $this->createQueryBuilder('i');
        $now = new DateTime();

        return $queryBuilder->where('i.code LIKE :code')
            ->andWhere('i.fromDate <= :now')
            ->andWhere(
                $queryBuilder->expr()
                ->orX(
                    $queryBuilder->expr()->isNull('i.tillDate'),
                    $queryBuilder->expr()->gte('i.tillDate', ':now')
                )
            )
            ->andWhere('i.hidden = :hidden')
            ->setParameters([
                'code' => $code,
                'now' => $now,
                'hidden' => false,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
