<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Common\Uri;
use Doctrine\ORM\EntityRepository;
use Backend\Core\Engine\Model;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class ProductRepository extends EntityRepository
{
    public function add(Product $product): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($product);
    }

    /**
     * @param int|null $id
     * @param Locale $locale
     * @return Product|null
     * @throws ProductNotFound
     */
    public function findOneByIdAndLocale(?int $id, Locale $locale): ?Product
    {
        if ($id === null) {
            throw ProductNotFound::forEmptyId();
        }

        /** @var Product $product */
        $product = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($product === null) {
            throw ProductNotFound::forId($id);
        }

        return $product;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (Product $product) {
                $this->getEntityManager()->remove($product);
            },
            (array)$this->findBy(['id' => $id, 'locale' => $locale])
        );
    }

    /**
     * Find the products limited by category
     *
     * @param Category $category
     * @param integer $limit
     * @param integer $offset
     * @param string $sorting
     *
     * @return Product[]
     */
    public function findLimitedByCategory(Category $category, int $limit, int $offset = 0, ?string $sorting)
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $query = $queryBuilder->where('p.category = :category')
                              ->setParameter('category', $category);

        $query = $this->setProductSorting($query, $sorting);


        return $query->setMaxResults($limit)
                     ->setFirstResult($offset)
                     ->getQuery()
                     ->getResult();
    }

    /**
     * Find a product by category and product url
     *
     * @param Locale $locale
     * @param string $category
     * @param string $url
     *
     * @return Product|null
     */
    public function findByCategoryAndUrl(Locale $locale, string $category, $url): ?Product
    {
        $queryBuilder         = $this->createQueryBuilder('i');
        $categoryQueryBuilder = $this->getEntityManager()->createQueryBuilder();

        $categoryQuery = $categoryQueryBuilder->select('c.id')
                                              ->from(Category::class, 'c')
                                              ->join(Meta::class, 'm2', 'WITH', 'm2.id = c.meta')
                                              ->where('m2.url = :category')
                                              ->andWhere('c.locale = :locale')
                                              ->setParameter('locale', $locale);

        return $queryBuilder->join(Meta::class, 'm', 'WITH', 'm.id = i.meta')
                            ->where('i.locale = :locale')
                            ->andWhere('m.url = :url')
                            ->andWhere(
                                $queryBuilder->expr()->in(
                                    'i.category',
                                    $categoryQuery->getDQL()
                                )
                            )
                            ->setParameters(
                                [
                                    'locale'   => $locale,
                                    'url'      => $url,
                                    'category' => $category
                                ]
                            )
                            ->getQuery()
                            ->getOneOrNullResult();
    }

    /**
     * Get the next sequence in line
     *
     * @param Locale $locale
     * @param Category $category
     *
     * @return integer
     */
    public function getNextSequence(Locale $locale, ?Category $category): int
    {
        $query_builder = $this->createQueryBuilder('i');
        $query_builder->select('MAX(i.sequence) as sequence')
                      ->where('i.locale = :locale')
                      ->setParameter('locale', $locale);

        // Include the parent if is set
        if ($category) {
            $query_builder->andWhere('i.category = :category');
            $query_builder->setParameter('category', $category);
        }

        // Return the new sequence
        return $query_builder->getQuery()
                             ->getSingleScalarResult() + 1;
    }

    /**
     * Count the products
     *
     * @param Locale $locale
     *
     * @return integer
     */
    public function getCount(Locale $locale): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return $query_builder->select('COUNT(i.id)')
                             ->where('i.locale = :locale')
                             ->setParameter('locale', $locale)
                             ->getQuery()
                             ->getSingleScalarResult();
    }

    public function findForAutoComplete(
        Locale $locale,
        string $query,
        ?int $excluded_id,
        ?int $page_limit = 10,
        ?int $page = 1
    ) {
        $queryBuilder = $this->createQueryBuilder('i');

        $queryBuilder->where('i.locale = :locale')
                     ->andWhere(
                         $queryBuilder->expr()->orX(
                             $queryBuilder->expr()->like('i.sku', ':query'),
                             $queryBuilder->expr()->like('i.title', ':query')
                         )
                     )
                     ->setParameter('locale', $locale)
                     ->setParameter('query', '%' . $query . '%');

        if ($excluded_id) {
            $queryBuilder->andWhere('i.id != :excluded_id')
                         ->setParameter('excluded_id', $excluded_id);
        }

        return $queryBuilder->getQuery()
                            ->setMaxResults($page_limit)
                            ->setFirstResult(($page - 1) * $page_limit)
                            ->getResult();
    }

    public function findByLocaleAndUrl(Locale $locale, string $url): Product
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->select('i')
                            ->join(Meta::class, 'm', 'WITH', 'm.id = i.meta')
                            ->where('i.locale = :locale')
                            ->andWhere('m.url = :url')
                            ->setParameters(
                                [
                                    'locale' => $locale,
                                    'url'    => $url
                                ]
                            )
                            ->getQuery()
                            ->getSingleResult();
    }

    /**
     * @param string $url
     * @param Locale $locale
     * @param integer $id
     *
     * @return string
     */
    public function getUrl($url, Locale $locale, $id)
    {
        $url           = Uri::getUrl((string)$url);
        $query_builder = $this->createQueryBuilder('i');
        $query_builder->join(Meta::class, 'm', 'WITH', 'm = i.meta')
                      ->where($query_builder->expr()->eq('m.url', ':url'))
                      ->andWhere($query_builder->expr()->eq('i.locale', ':locale'))
                      ->setParameters(
                          [
                              'url'    => $url,
                              'locale' => $locale
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
     * Filter the products based on specification values
     *
     * @param array $filters
     * @param Category $category
     * @param integer $limit
     * @param integer $offset
     * @param string $sorting
     *
     * @return array
     */
    public function filterProducts(array $filters, Category $category, int $limit, int $offset, string $sorting): array
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $query = $queryBuilder->innerJoin('p.category', 'c')
                              ->where('c.id = :category');

        // Counter needed for the parameters
        $i = 0;
        foreach ($filters as $specification => $specificationValue) {
            $queryBuilder2 = $this->getEntityManager()->createQueryBuilder();

            // Prepare our IN query
            $query2 = $queryBuilder2->select('p'.$i.'.id')
                                    ->from(Specification::class, 's'.$i)
                                    ->leftJoin('s'.$i.'.specification_values', 'sv'.$i)
                                    ->leftJoin('sv'.$i.'.products', 'p'.$i)
                                    ->innerJoin('s'.$i.'.meta', 'm'.$i)
                                    ->innerJoin('sv'.$i.'.meta', 'm_'.$i)
                                    ->where('s'.$i.'.filter = :filter')
                                    ->andWhere('p'.$i.'.category = :category')
                                    ->andWhere('m'.$i.'.url = :specification' . $i)
                                    ->andWhere($queryBuilder->expr()->in('m_'.$i.'.url', $specificationValue));

            // Add the IN query to our root query
            $query->andWhere($queryBuilder->expr()->in('p.id', $query2->getDql()));

            // Set the specification parameters
            $query->setParameter('specification' . $i, $specification);

            // Update the counter
            $i++;
        }

        // Set the parameters
        $query->setParameter('category', $category)
              ->setParameter('filter', true)
              ->setMaxResults($limit)
              ->setFirstResult($offset);

        $query = $this->setProductSorting($query, $sorting);

        return $query->getQuery()
                     ->getResult();
    }

    /**
     * Filter the products and count the result
     *
     * @param array $filters
     * @param Category $category
     *
     * @return integer
     */
    public function filterProductsCount(array $filters, Category $category): int
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $query = $queryBuilder->select('count(p.id)')
                              ->innerJoin('p.category', 'c')
                              ->where('c.id = :category');

        // We need a counter to set the parameters
        $i = 0;
        foreach ($filters as $specification => $specificationValue) {
            $queryBuilder2 = $this->getEntityManager()->createQueryBuilder();

            // Prepare our IN query
            $query2 = $queryBuilder2->select('p'.$i.'.id')
                                    ->from(Specification::class, 's'.$i)
                                    ->leftJoin('s'.$i.'.specification_values', 'sv'.$i)
                                    ->leftJoin('sv'.$i.'.products', 'p'.$i)
                                    ->innerJoin('s'.$i.'.meta', 'm'.$i)
                                    ->innerJoin('sv'.$i.'.meta', 'm_'.$i)
                                    ->where('s'.$i.'.filter = :filter')
                                    ->andWhere('p'.$i.'.category = :category')
                                    ->andWhere('m'.$i.'.url = :specification' . $i)
                                    ->andWhere($queryBuilder->expr()->in('m_'.$i.'.url', $specificationValue));

            // Add the IN query to our root query
            $query->andWhere($queryBuilder->expr()->in('p.id', $query2->getDql()));

            // Set the specification parameters
            $query->setParameter('specification' . $i, $specification);

            $i++;
        }

        // Set the parameters
        $query->setParameter('category', $category)
              ->setParameter('filter', true);

        try {
            return $query->getQuery()
                         ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @param QueryBuilder $query
     * @param string $sorting
     *
     * @return QueryBuilder
     */
    private function setProductSorting(QueryBuilder $query, string $sorting): QueryBuilder
    {
        switch ($sorting) {
            case Product::SORT_RANDOM:
            default:
                $query->orderBy('p.sequence', 'ASC')
                      ->addOrderBy('p.id', 'DESC');
                break;
            case Product::SORT_PRICE_ASC:
                $query->orderBy('p.price', 'ASC');
                break;
            case Product::SORT_PRICE_DESC:
                $query->orderBy('p.price', 'DESC');
                break;
            case Product::SORT_CREATED_AT:
                $query->orderBy('p.createdOn', 'DESC');
                break;
        }

        return $query;
    }
}
