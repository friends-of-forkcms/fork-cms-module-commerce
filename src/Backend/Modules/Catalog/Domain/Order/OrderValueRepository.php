<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Modules\Catalog\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\ContentBlocks\Domain\ContentBlock\Exception\ContentBlockNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class OrderValueRepository extends EntityRepository
{

}
