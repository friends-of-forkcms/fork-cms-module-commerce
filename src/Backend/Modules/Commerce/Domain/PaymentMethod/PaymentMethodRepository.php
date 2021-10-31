<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Backend\Modules\Commerce\Domain\PaymentMethod\Exception\PaymentMethodNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class PaymentMethodRepository extends EntityRepository
{
    public function add(PaymentMethod $paymentMethod): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($paymentMethod);
    }

    public function findOneByIdAndLocale(?int $id, Locale $locale): ?PaymentMethod
    {
        if ($id === null) {
            throw PaymentMethodNotFound::forEmptyId();
        }

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($paymentMethod === null) {
            throw PaymentMethodNotFound::forId($id);
        }

        return $paymentMethod;
    }

    public function findOneById(int $id): ?PaymentMethod
    {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->find($id);

        if ($paymentMethod === null) {
            throw PaymentMethodNotFound::forId($id);
        }

        return $paymentMethod;
    }

    public function findEnabledPaymentMethods(Locale $locale): array
    {
        $result = $this
                    ->createQueryBuilder('i')
                    ->select('i.name')
                    ->andWhere('i.locale = :locale')
                    ->andWhere('i.isEnabled = :isEnabled')
                    ->setParameter('locale', $locale)
                    ->setParameter('isEnabled', true)
                    ->getQuery()
                    ->getScalarResult();

        return array_column($result, 'name');
    }
}
