<?php

namespace Backend\Modules\Commerce\Domain\Country;

use Backend\Modules\Commerce\Domain\Country\Exception\CountryNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository
{
    public function add(Country $country): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($country);
    }

    /**
     * @throws CountryNotFound
     */
    public function findOneByIdAndLocale(?int $id, Locale $locale): ?Country
    {
        if ($id === null) {
            throw CountryNotFound::forEmptyId();
        }

        /** @var Country $country */
        $country = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($country === null) {
            throw CountryNotFound::forId($id);
        }

        return $country;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (Country $country) {
                $this->getEntityManager()->remove($country);
            },
            (array) $this->findBy(['id' => $id, 'locale' => $locale])
        );
    }
}
