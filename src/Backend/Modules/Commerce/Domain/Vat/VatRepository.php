<?php

namespace Backend\Modules\Commerce\Domain\Vat;

use Backend\Modules\Commerce\Domain\Vat\Exception\VatNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class VatRepository extends EntityRepository
{
    public function add(Vat $vat): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($vat);
    }

    /**
     * @throws VatNotFound
     */
    public function findOneByIdAndLocale(?int $id, Locale $locale): ?Vat
    {
        if ($id === null) {
            throw VatNotFound::forEmptyId();
        }

        /** @var Vat $vat */
        $vat = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($vat === null) {
            throw VatNotFound::forId($id);
        }

        return $vat;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (Vat $vat) {
                $this->getEntityManager()->remove($vat);
            },
            (array) $this->findBy(['id' => $id, 'locale' => $locale])
        );
    }
}
