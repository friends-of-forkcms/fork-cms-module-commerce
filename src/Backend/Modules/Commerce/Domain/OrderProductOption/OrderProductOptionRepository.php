<?php

namespace Backend\Modules\Commerce\Domain\OrderProductOption;

use Doctrine\ORM\EntityRepository;

class OrderProductOptionRepository extends EntityRepository
{
    public function add(OrderProductOption $orderVat): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderVat);
    }
}
