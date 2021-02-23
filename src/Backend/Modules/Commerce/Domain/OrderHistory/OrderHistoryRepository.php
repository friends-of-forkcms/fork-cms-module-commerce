<?php

namespace Backend\Modules\Commerce\Domain\OrderHistory;

use Backend\Modules\Commerce\Domain\OrderHistory\Exception\OrderHistoryNotFound;
use Doctrine\ORM\EntityRepository;

class OrderHistoryRepository extends EntityRepository
{
    public function add(OrderHistory $orderHistory): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderHistory);
    }

    public function findOneById(?int $id): ?OrderHistory
    {
        if ($id === null) {
            throw OrderHistoryNotFound::forEmptyId();
        }

        /** @var OrderHistory $orderHistory */
        $orderHistory = $this->findOneBy(['id' => $id]);

        if ($orderHistory === null) {
            throw OrderHistoryNotFound::forId($id);
        }

        return $orderHistory;
    }

    public function removeById($id): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (OrderHistory $orderHistory) {
                $this->getEntityManager()->remove($orderHistory);
            },
            (array) $this->findBy(['id' => $id])
        );
    }
}
