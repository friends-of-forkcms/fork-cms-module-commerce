<?php

namespace Backend\Modules\Catalog\Domain\Search;

use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class SearchRepository extends EntityRepository
{

}
