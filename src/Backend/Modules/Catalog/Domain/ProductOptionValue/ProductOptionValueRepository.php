<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue;

use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOptionValue\Exception\ProductOptionValueNotFound;
use Doctrine\ORM\EntityRepository;

class ProductOptionValueRepository extends EntityRepository
{
    public function add(ProductOptionValue $product): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($product);
    }

    /**
     * @param int|null $id
     * @return ProductOptionValue|null
     * @throws ProductOptionValueNotFound
     */
    public function findOneById(?int $id): ?ProductOptionValue
    {
        if ($id === null) {
            throw ProductOptionValueNotFound::forEmptyId();
        }

        /** @var ProductOptionValue $product */
        $entity = $this->findOneBy(['id' => $id]);

        if ($entity === null) {
            throw ProductOptionValueNotFound::forId($id);
        }

        return $entity;
    }

    public function removeById($id): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (ProductOptionValue $entity) {
                $this->getEntityManager()->remove($entity);
            },
            (array)$this->findBy(['id' => $id])
        );
    }

    /**
     * Get the next sequence in line
     *
     * @param ProductOption $productOption
     *
     * @throws
     *
     * @return integer
     */
    public function getNextSequence(ProductOption $productOption): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return (int) $query_builder->select('MAX(i.sequence) as sequence')
                ->where('i.product_option = :product_option')
                ->setParameter('product_option', $productOption)
                ->getQuery()
                ->getSingleScalarResult() + 1;
    }
}
