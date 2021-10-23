<?php

namespace Backend\Modules\Commerce\Domain\ProductDimension;

use Backend\Modules\Commerce\Domain\Product\Product;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class ProductDimensionRepository extends EntityRepository
{
    public function findByProductAndDimensions(Product $product, int $width, int $height): ?ProductDimension
    {
        $queryBuilder = $this->createQueryBuilder('i');

        $query = $queryBuilder->innerJoin('i.product', 'p')
            ->where('i.product = :product')
            ->andWhere('p.min_width <= :width')
            ->andWhere('p.max_width >= :width')
            ->andWhere('p.min_height <= :height')
            ->andWhere('p.max_height >= :height')
            ->andWhere(':width <= i.width')
            ->andWhere(':height <= i.height')
            ->addOrderBy('i.width', 'ASC')
            ->addOrderBy('i.height', 'ASC')
            ->setMaxResults(1)
            ->setParameters([
                'product' => $product,
                'width' => $width,
                'height' => $height,
            ]);

        try {
            return $query->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}
