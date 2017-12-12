<?php

namespace Backend\Modules\Catalog\Domain\Quote;

use Doctrine\ORM\EntityRepository;

class QuoteAddressRepository extends EntityRepository
{
    public function add(QuoteAddress $quoteAddress): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($quoteAddress);
    }
}
