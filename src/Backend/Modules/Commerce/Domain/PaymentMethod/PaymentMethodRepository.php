<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Backend\Modules\Commerce\Domain\PaymentMethod\Exception\PaymentMethodNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class PaymentMethodRepository extends EntityRepository
{
    public function add(PaymentMethod $paymentMethod): void
    {
        $this->getEntityManager()->persist($paymentMethod);
        $this->getEntityManager()->flush();
    }

    public function findOneByNameAndLocale(?string $name, Locale $locale): ?PaymentMethod
    {
        if ($name === null) {
            throw PaymentMethodNotFound::forEmptyName();
        }

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->findOneBy(['name' => $name, 'locale' => $locale]);

        if ($paymentMethod === null) {
            throw PaymentMethodNotFound::forName($name);
        }

        return $paymentMethod;
    }

    public function removeByNameAndLocale($name, Locale $locale): void
    {
        array_map(
            function (PaymentMethod $paymentMethod) {
                $this->getEntityManager()->remove($paymentMethod);
                $this->getEntityManager()->flush();
            },
            (array)$this->findBy(['name' => $name, 'locale' => $locale])
        );
    }

    public function findInstalledPaymentMethods(Locale $locale): array
    {
        $result = $this->createQueryBuilder('i')
                       ->select('i.name')
                       ->where('i.locale = :locale')
                       ->setParameter('locale', $locale)
                       ->getQuery()
                       ->getScalarResult();

        return array_column($result, 'name');
    }
}
