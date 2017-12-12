<?php

namespace Backend\Modules\Catalog\Domain\OrderProduct;

use Doctrine\ORM\EntityRepository;

class OrderProductRepository extends EntityRepository
{
    public function add(OrderProduct $orderVat): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderVat);
    }
}
