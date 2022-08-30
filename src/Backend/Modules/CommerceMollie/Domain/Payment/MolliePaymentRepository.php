<?php

namespace Backend\Modules\CommerceMollie\Domain\Payment;

use Doctrine\ORM\EntityRepository;

class MolliePaymentRepository extends EntityRepository
{
    public function add(MolliePayment $payment): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($payment);
    }

    public function findOneByOrderId(int $order_id): ?MolliePayment
    {
        /** @var MolliePayment $payment */
        $payment = $this->findOneBy(['order_id' => $order_id]);

        return $payment;
    }
}
