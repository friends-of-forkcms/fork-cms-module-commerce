<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus;

use Backend\Modules\Commerce\Domain\OrderStatus\Exception\OrderStatusNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class OrderStatusRepository extends EntityRepository
{
    /**
     * @param OrderStatus $orderStatus
     * @throws \Doctrine\ORM\ORMException
     */
    public function add(OrderStatus $orderStatus): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderStatus);
    }

    /**
     * @param int|null $id
     * @param Locale $locale
     * @return OrderStatus|null
     * @throws OrderStatusNotFound
     */
    public function findOneByIdAndLocale(?int $id, Locale $locale): ?OrderStatus
    {
        if ($id === null) {
            throw OrderStatusNotFound::forEmptyId();
        }

        /** @var OrderStatus $orderStatus */
        $orderStatus = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($orderStatus === null) {
            throw OrderStatusNotFound::forId($id);
        }

        return $orderStatus;
    }

    /**
     * @param int $id
     * @return OrderStatus
     * @throws OrderStatusNotFound
     */
    public function findOneById(int $id): OrderStatus
    {
        /** @var OrderStatus $orderStatus */
        $orderStatus = $this->findOneBy(['id' => $id]);

        if ($orderStatus === null) {
            throw OrderStatusNotFound::forId($id);
        }

        return $orderStatus;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (OrderStatus $orderStatus) {
                $this->getEntityManager()->remove($orderStatus);
            },
            (array) $this->findBy(['id' => $id, 'locale' => $locale])
        );
    }

    public function findByLocale(Locale $locale): array
    {
        return $this->findBy(
            [
                'locale' => $locale
            ],
            [
                'title' => 'ASC'
            ]
        );
    }
}
