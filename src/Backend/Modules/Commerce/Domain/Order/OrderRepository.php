<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class OrderRepository extends EntityRepository
{
    public function add(Order $order): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($order);
    }

    /**
     * @param int|null $id
     *
     * @return Order|null
     * @throws OrderNotFound
     */
    public function findOneById(?int $id): ?Order
    {
        if ($id === null) {
            throw OrderNotFound::forEmptyId();
        }

        /** @var Order $order */
        $order = $this->findOneBy(['id' => $id]);

        if ($order === null) {
            throw OrderNotFound::forId($id);
        }

        return $order;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (Order $order) {
                $this->getEntityManager()->remove($order);
            },
            (array) $this->findBy(['id' => $id, 'locale' => $locale])
        );
    }

    /**
     * @param int $id
     * @param Account $account
     * @return Order
     * @throws OrderNotFound
     */
    public function findByIdAndAccount(int $id, Account $account): Order
    {
        $query_builder = $this->createQueryBuilder('i');

        try {
            return $query_builder->where('i.id = :id')
                ->andWhere('i.account = :account')
                ->setParameters([
                    'id' => $id,
                    'account' => $account,
                ])
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            throw OrderNotFound::forId($id);
        } catch (NonUniqueResultException $e) {
            throw OrderNotFound::forId($id);
        }
    }

    /**
     * @param int $id
     * @param string $email
     * @return Order
     * @throws OrderNotFound
     */
    public function findByIdAndEmailAddress(int $id, string $email): Order
    {
        $query_builder = $this->createQueryBuilder('i');

        try {
            return $query_builder->join('i.account', 'a')
                ->where('i.id = :id')
                ->andWhere('a.email = :email')
                ->setParameters([
                    'id' => $id,
                    'email' => $email,
                ])
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            throw OrderNotFound::forId($id);
        } catch (NonUniqueResultException $e) {
            throw OrderNotFound::forId($id);
        }
    }

    /**
     * @param int $status
     *
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getStatusCount(int $status): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return $query_builder->select('COUNT(i.id) as order_count')
                             ->andWhere('i.status = :status')
                             ->setParameter('status', $status)
                             ->getQuery()
                             ->getSingleScalarResult();
    }
}
