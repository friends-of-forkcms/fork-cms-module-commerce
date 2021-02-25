<?php

namespace Backend\Modules\Commerce\Domain\OrderProductNotification;

use Doctrine\ORM\EntityRepository;

class OrderProductNotificationRepository extends EntityRepository
{
    public function add(OrderProductNotification $orderVat): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderVat);
    }
}
