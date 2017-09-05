<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\Cart\Exception\CartNotFound;
use Backend\Modules\ContentBlocks\Domain\ContentBlock\Exception\ContentBlockNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;

class CartValueRepository extends EntityRepository
{

}
