<?php

namespace Backend\Modules\Catalog\Domain\UpSellProduct;

use Doctrine\ORM\EntityRepository;

class UpSellProductRepository extends EntityRepository
{
    public function add(UpSellProduct $upSellproduct): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($upSellproduct);
    }
}
