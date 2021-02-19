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

    /**
     * @param int $id
     * @param ProductOption $productOption
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByIdAndProductOption(int $id, ProductOption $productOption): ?ProductOptionValue
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->join('i.productOption', 'po')
            ->where('po.id = :productOption')
            ->where('i.id = :id')
            ->setParameters([
                'productOption' => $productOption,
                'id' => $id,
            ])
            ->getQuery()
            ->getOneOrNullResult();
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

    public function findForAutoComplete(
        string $query,
        int $productOptionId,
        ?int $page_limit = 10,
        ?int $page = 1
    ) {
        $queryBuilder = $this->createQueryBuilder('i');

        $queryBuilder->join('i.product_option', 'po')
            ->where('po.id = :product_option_id')
            ->andWhere('i.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('product_option_id', $productOptionId);

        return $queryBuilder->getQuery()
            ->setMaxResults($page_limit)
            ->setFirstResult(($page - 1) * $page_limit)
            ->getResult();
    }
}
