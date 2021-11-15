<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Backend\Modules\Commerce\Domain\ShipmentMethod\Exception\ShipmentMethodNotFound;
use Common\Locale;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

class ShipmentMethodRepository extends EntityRepository
{
    public function add(ShipmentMethod $shipmentMethod): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($shipmentMethod);
    }

    public function findOneByIdAndLocale(?int $id, Locale $locale): ?ShipmentMethod
    {
        if ($id === null) {
            throw ShipmentMethodNotFound::forEmptyId();
        }

        /** @var ShipmentMethod $paymentMethod */
        $paymentMethod = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($paymentMethod === null) {
            throw ShipmentMethodNotFound::forId($id);
        }

        return $paymentMethod;
    }

    /**
     * @param Locale $locale
     * @return array<int, ShipmentMethod>
     */
    public function findEnabledShipmentMethods(Locale $locale): array
    {
        return $this
            ->createQueryBuilder('i')
            ->select('i')
            ->andWhere('i.locale = :locale')
            ->andWhere('i.isEnabled = :isEnabled')
            ->setParameter('locale', $locale)
            ->setParameter('isEnabled', true)
            ->getQuery()
            ->getResult();
    }
}
