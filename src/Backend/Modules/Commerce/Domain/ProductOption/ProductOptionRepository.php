<?php

namespace Backend\Modules\Commerce\Domain\ProductOption;

use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\ProductOption\Exception\ProductOptionNotFound;
use Doctrine\ORM\EntityRepository;

class ProductOptionRepository extends EntityRepository
{
    public function add(ProductOption $productOption): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($productOption);
    }

    /**+
     * @param int|null $id
     * @return Product|null
     * @throws ProductOptionNotFound
     */
    public function findOneById(?int $id): ?ProductOption
    {
        if ($id === null) {
            throw ProductOptionNotFound::forEmptyId();
        }

        /** @var Product $product */
        $product = $this->findOneBy(['id' => $id]);

        if ($product === null) {
            throw ProductOptionNotFound::forId($id);
        }

        return $product;
    }

    public function removeById($id): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (ProductOption $productOption) {
                $this->getEntityManager()->remove($productOption);
            },
            (array) $this->findBy(['id' => $id])
        );
    }

    /**
     * Get the next sequence in line.
     *
     * @throws
     */
    public function getNextSequence(Product $product): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return (int) $query_builder->select('MAX(i.sequence) as sequence')
                ->where('i.product = :product')
                ->setParameter('product', $product)
                ->getQuery()
                ->getSingleScalarResult() + 1;
    }
}
