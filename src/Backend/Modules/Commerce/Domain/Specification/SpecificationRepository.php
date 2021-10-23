<?php

namespace Backend\Modules\Commerce\Domain\Specification;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Specification\Exception\SpecificationNotFound;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Common\Uri;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class SpecificationRepository extends EntityRepository
{
    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function add(Specification $specification): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($specification);
    }

    /**
     * @throws SpecificationNotFound
     */
    public function findOneByIdAndLocale(?int $id, Locale $locale): ?Specification
    {
        if ($id === null) {
            throw SpecificationNotFound::forEmptyId();
        }

        /** @var Specification $specification */
        $specification = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($specification === null) {
            throw SpecificationNotFound::forId($id);
        }

        return $specification;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (Specification $specification) {
                $this->getEntityManager()->remove($specification);
            },
            (array) $this->findBy(['id' => $id, 'locale' => $locale])
        );
    }

    /**
     * Get the next sequence in line.
     *
     * @throws NonUniqueResultException
     */
    public function getNextSequence(Locale $locale): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return $query_builder->select('MAX(i.sequence) as sequence')
                ->where('i.locale = :locale')
                ->setParameter('locale', $locale)
                ->getQuery()
                ->getSingleScalarResult() + 1;
    }

    /**
     * @param string $url
     * @param int    $id
     *
     * @return string
     */
    public function getUrl($url, Locale $locale, $id)
    {
        $url = Uri::getUrl((string) $url);
        $query_builder = $this->createQueryBuilder('i');
        $query_builder->join(Meta::class, 'm', 'WITH', 'm = i.meta')
            ->where($query_builder->expr()->eq('m.url', ':url'))
            ->andWhere($query_builder->expr()->eq('i.locale', ':locale'))
            ->setParameters(
                [
                    'url' => $url,
                    'locale' => $locale,
                ]
            );

        if ($id !== null) {
            $query_builder->andWhere($query_builder->expr()->neq('i.id', ':id'))
                ->setParameter('id', $id);
        }

        if (count($query_builder->getQuery()->getResult())) {
            $url = Model::addNumber($url);

            return self::getURL($url, $locale, $id);
        }

        return $url;
    }

    /**
     * Find the specification filters based on the given category.
     */
    public function findFiltersByCategory(Category $category): array
    {
        $queryBuilder = $this->createQueryBuilder('s');

        return $queryBuilder->select(['s', 'sv'])
            ->leftJoin('s.specification_values', 'sv')
            ->leftJoin('sv.products', 'p')
            ->where('s.filter = :filter')
            ->andWhere('p.category = :category')
            ->orderBy('s.sequence', 'ASC')
            ->setParameter('filter', true)
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the specifications for a product.
     */
    public function findByProduct(Product $product): array
    {
        $queryBuilder = $this->createQueryBuilder('s');

        return $queryBuilder->select(['s', 'sv'])
            ->innerJoin('s.specification_values', 'sv')
            ->innerJoin('sv.products', 'p')
            ->where('p.id = :product')
            ->orderBy('s.sequence', 'ASC')
            ->setParameter('product', $product->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the specification filters based on the given category.
     */
    public function findFiltersBySearchTerm(string $searchTerm): array
    {
        $queryBuilder = $this->createQueryBuilder('s');

        return $queryBuilder->select(['s', 'sv'])
            ->leftJoin('s.specification_values', 'sv')
            ->leftJoin('sv.products', 'p')
            ->where('s.filter = :filter')
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('p.title', ':search_term')
            ))
            ->orderBy('s.sequence', 'ASC')
            ->setParameter('filter', true)
            ->setParameter('search_term', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }
}
