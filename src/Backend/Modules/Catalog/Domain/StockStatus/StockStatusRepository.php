<?php

namespace Backend\Modules\Catalog\Domain\StockStatus;

use Backend\Modules\Catalog\Domain\StockStatus\Exception\StockStatusNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class StockStatusRepository extends EntityRepository
{
    public function add(StockStatus $stockStatus): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($stockStatus);
    }

    public function findOneByIdAndLocale(?int $id, Locale $locale): ?StockStatus
    {
        if ($id === null) {
            throw StockStatusNotFound::forEmptyId();
        }

        /** @var StockStatus $stockStatus */
        $stockStatus = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($stockStatus === null) {
            throw StockStatusNotFound::forId($id);
        }

        return $stockStatus;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (StockStatus $stockStatus) {
                $this->getEntityManager()->remove($stockStatus);
            },
            (array) $this->findBy(['id' => $id, 'locale' => $locale])
        );
    }
}
