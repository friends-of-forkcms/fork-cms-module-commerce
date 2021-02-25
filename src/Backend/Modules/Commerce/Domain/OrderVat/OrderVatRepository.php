<?php

namespace Backend\Modules\Commerce\Domain\OrderVat;

use Doctrine\ORM\EntityRepository;

class OrderVatRepository extends EntityRepository
{
    public function add(OrderVat $orderVat): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderVat);
    }
}
