<?php

namespace Backend\Modules\Commerce\Domain\OrderProduct;

use Backend\Modules\Commerce\Domain\OrderProduct\Exception\OrderProductNotFound;
use Doctrine\ORM\EntityRepository;

class OrderProductRepository extends EntityRepository
{
    public function add(OrderProduct $orderVat): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderVat);
    }

    /**
     * @param int|null $id
     *
     * @return OrderProduct|null
     * @throws OrderProductNotFound
     */
    public function findOneById(?int $id): ?OrderProduct
    {
        if ($id === null) {
            throw OrderProductNotFound::forEmptyId();
        }

        /** @var OrderProduct $orderProduct */
        $orderProduct = $this->findOneBy(['id' => $id]);

        if ($orderProduct === null) {
            throw OrderProductNotFound::forId($id);
        }

        return $orderProduct;
    }
}
