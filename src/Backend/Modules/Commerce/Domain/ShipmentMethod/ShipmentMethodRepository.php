<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Backend\Modules\Commerce\Domain\ShipmentMethod\Exception\ShipmentMethodNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class ShipmentMethodRepository extends EntityRepository
{
    public function add(ShipmentMethod $shipmentMethod): void
    {
        $this->getEntityManager()->persist($shipmentMethod);
        $this->getEntityManager()->flush();
    }

    public function findOneByNameAndLocale(?string $name, Locale $locale): ?ShipmentMethod
    {
        if ($name === null) {
            throw ShipmentMethodNotFound::forEmptyName();
        }

        /** @var ShipmentMethod $shipmentMethod */
        $shipmentMethod = $this->findOneBy(['name' => $name, 'locale' => $locale]);

        if ($shipmentMethod === null) {
            throw ShipmentMethodNotFound::forName($name);
        }

        return $shipmentMethod;
    }

    public function removeByNameAndLocale($name, Locale $locale): void
    {
        array_map(
            function (ShipmentMethod $shipmentMethod) {
                $this->getEntityManager()->remove($shipmentMethod);
                $this->getEntityManager()->flush();
            },
            (array) $this->findBy(['name' => $name, 'locale' => $locale])
        );
    }

    public function findInstalledShipmentMethods(Locale $locale): array
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
