<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress;

use Doctrine\ORM\EntityRepository;

class OrderAddressRepository extends EntityRepository
{
    public function add(OrderAddress $orderAddress): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderAddress);
    }
}
