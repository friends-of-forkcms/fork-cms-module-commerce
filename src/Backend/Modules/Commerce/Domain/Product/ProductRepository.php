<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Common\Uri;
use Doctrine\ORM\EntityRepository;
use Backend\Core\Engine\Model;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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

    /**
     * @param int|null $id
     * @param Locale $locale
     * @return Product|null
     * @throws ProductNotFound
     */
    public function findOneActiveByIdAndLocale(?int $id, Locale $locale): ?Product
    {
        if ($id === null) {
            throw ProductNotFound::forEmptyId();
        }

        /** @var Product $product */
        $product = $this->findOneBy(['id' => $id, 'locale' => $locale, 'hidden' => false]);

        if ($product === null) {
            throw ProductNotFound::forId($id);
        }

        return $product;
    }

    /**
     * @return Product[]
     */
    public function findActive()
    {
        /** @var Product $product */
        return $this->findBy(['hidden' => false]);
    }

    /**
     * @param Locale $locale
     * @return Product[]
     */
    public function findActiveByLocaleAndWithGoogleTaxonomyId(Locale $locale)
    {
        $queryBuilder = $this->createQueryBuilder('p');

        return $queryBuilder->join('p.category', 'c')
            ->where($queryBuilder->expr()->isNotNull('c.googleTaxonomyId'))
            ->andWhere('p.locale = :locale')
            ->andWhere('p.hidden = :hidden')
            ->setParameters([
                'locale' => $locale,
                'hidden' => false,
            ])
            ->getQuery()
            ->getResult();
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
     * Find a product by category and product url
     *
     * @param Locale $locale
     * @param string $category
     * @param string $url
     *
     * @return Product|null
     * @throws NonUniqueResultException
     *
     */
    public function findByCategoryAndUrl(Locale $locale, string $category, $url): ?Product
    {
        $queryBuilder = $this->createQueryBuilder('i');
        $categoryQueryBuilder = $this->getEntityManager()->createQueryBuilder();

        $categoryQuery = $categoryQueryBuilder->select('c.id')
            ->from(Category::class, 'c')
            ->join(Meta::class, 'm2', 'WITH', 'm2.id = c.meta')
            ->where('m2.url = :category')
            ->andWhere('c.locale = :locale')
            ->setParameter('locale', $locale);

        return $queryBuilder->join(Meta::class, 'm', 'WITH', 'm.id = i.meta')
            ->where('i.locale = :locale')
            ->andWhere('i.hidden = :hidden')
            ->andWhere('m.url = :url')
            ->andWhere(
                $queryBuilder->expr()->in(
                    'i.category',
                    $categoryQuery->getDQL()
                )
            )
            ->setParameters([
                'locale' => $locale,
                'hidden' => false,
                'url' => $url,
                'category' => $category,
            ])
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
     * @throws NonUniqueResultException
     *
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
     * @throws NonUniqueResultException
     *
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
    )
    {
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

    /**
     * Find a product based on the url part
     *
     * @param Locale $locale
     * @param string $url
     *
     * @return Product
     * @throws NonUniqueResultException
     *
     * @throws NoResultException
     */
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
                    'url' => $url
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
        $url = Uri::getUrl((string)$url);
        $query_builder = $this->createQueryBuilder('i');
        $query_builder->join(Meta::class, 'm', 'WITH', 'm = i.meta')
            ->where($query_builder->expr()->eq('m.url', ':url'))
            ->andWhere($query_builder->expr()->eq('i.locale', ':locale'))
            ->setParameters(
                [
                    'url' => $url,
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
     * Find the products limited by category
     *
     * @param Category $category
     * @param integer $limit
     * @param integer $offset
     * @param string $sorting
     *
     * @return Product[]
     */
    public function findLimitedByCategory(Category $category, int $limit, int $offset = 0, string $sorting = Product::SORT_RANDOM)
    {
        $sql = 'SELECT p.* FROM commerce_products p WHERE p.category_id = :category AND p.hidden = :hidden';
        $parameters = [
            'category' => $category,
            'hidden' => false,
        ];

        $this->setProductSorting($sql, $sorting);
        $sql .= ' LIMIT ' . $offset . ', ' . $limit;

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters($parameters);

        return $query->getResult();
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
        $sql = 'SELECT p.* FROM commerce_products p WHERE p.category_id = :category AND p.hidden = :hidden';
        $parameters = [
            'category' => $category,
            'hidden' => false,
        ];

        $this->buildFilterQuery($sql, $parameters, $filters);
        $this->setProductSorting($sql, $sorting);
        $sql .= ' LIMIT ' . $offset . ', ' . $limit;

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters($parameters);

        return $query->getResult();
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
        $sql = 'SELECT count(p.id) as total_products FROM commerce_products p WHERE p.category_id = :category AND p.hidden = :hidden';
        $parameters = [
            'category' => $category->getId(),
            'hidden' => false,
        ];

        $this->buildFilterQuery($sql, $parameters, $filters);

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($parameters);

        return (int)$stmt->fetchColumn(0);
    }

    /**
     * Filter the products based on specification values and search string
     *
     * @param string $searchTerm
     * @param array $filters
     * @param integer $limit
     * @param integer $offset
     * @param string $sorting
     *
     * @return array
     */
    public function filterSearchedProducts(string $searchTerm, array $filters, int $limit, int $offset, string $sorting): array
    {
        $sql = 'SELECT p.* FROM commerce_products p WHERE ';
        $parameters = [];

        $this->buildSearchQuery('p', $sql, $searchTerm, $parameters);
        $this->buildFilterQuery($sql, $parameters, $filters);
        $this->setProductSorting($sql, $sorting);
        $sql .= ' LIMIT ' . $offset . ', ' . $limit;

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters($parameters);

        return $query->getResult();
    }

    /**
     * Filter and search the products and count the result
     *
     * @param string $searchTerm
     * @param array $filters
     *
     * @return integer
     */
    public function filterSearchedProductsCount(string $searchTerm, array $filters): int
    {
        $sql = 'SELECT p.* FROM commerce_products p WHERE ';
        $parameters = [];

        $this->buildSearchQuery('p', $sql, $searchTerm, $parameters);
        $this->buildFilterQuery($sql, $parameters, $filters);

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($parameters);

        return (int)$stmt->fetchColumn(0);
    }

    /**
     * Search the products by the given search string
     *
     * @param string $searchTerm
     * @param integer $limit
     * @param integer $offset
     * @param string $sorting
     *
     * @return Product[]
     */
    public function searchProductsLimited(string $searchTerm, int $limit, int $offset = 0, ?string $sorting)
    {
        $sql = 'SELECT p.* FROM commerce_products p WHERE ';
        $parameters = [];

        $this->buildSearchQuery('p', $sql, $searchTerm, $parameters);
        $this->setProductSorting($sql, $sorting);
        $sql .= ' LIMIT ' . $offset . ', ' . $limit;

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters($parameters);

        return $query->getResult();
    }

    /**
     * Count the products
     *
     * @param string $searchTerm
     * @param Locale $locale
     *
     * @return integer
     */
    public function getSearchProductCount(string $searchTerm, Locale $locale): int
    {
        $sql = 'SELECT p.* FROM commerce_products p WHERE ';
        $parameters = [];

        $this->buildSearchQuery('p', $sql, $searchTerm, $parameters);

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($parameters);

        return (int)$stmt->fetchColumn(0);
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @param array $filters
     */
    public function buildFilterQuery(string &$sql, array &$parameters, array $filters): void
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        // Remove not available filters from list
        $availableFilters = array_map(
            function ($item) {
                return $item['url'];
            },
            $queryBuilder->select('m.url')
                ->from(Specification::class, 's')
                ->join('s.meta', 'm')
                ->where('s.filter = :filter')
                ->andWhere(
                    $queryBuilder->expr()->in('m.url', array_keys($filters))
                )
                ->setParameters([
                    'filter' => true,
                ])
                ->getQuery()
                ->getScalarResult()
        );

        // Counter needed for the parameters
        $i = 0;
        foreach ($filters as $specification => $specificationValue) {
            if (!in_array($specification, $availableFilters)) {
                continue;
            }

            $parameters['specification' . $i] = $specification;

            // Split the specification values
            $specificationValuesPlaceholder = [];
            $j = 0;
            foreach ($specificationValue as $value) {
                $key = 'specificationValue' . $i . '_' . $j;
                $specificationValuesPlaceholder[] = ':' . $key;
                $parameters[$key] = $value;
                $j++;
            }

            $sql .= ' AND p.id IN(
                SELECT psv.product_id
                FROM commerce_specification_values sv
                INNER JOIN commerce_products_specification_values psv ON psv.specification_value_id = sv.id
                INNER JOIN meta svmeta ON svmeta.id = sv.meta_id
                INNER JOIN commerce_specifications s ON s.id = sv.`specification_id`
                INNER JOIN meta smeta ON smeta.id = s.meta_id
                WHERE s.filter = 1
                AND smeta.url = :specification' . $i . '
                AND svmeta.url IN (' . implode(', ', $specificationValuesPlaceholder) . ')
            )';

            // Update the counter
            $i++;
        }
    }

    /**
     * @param string $query
     * @param string $sorting
     */
    private function setProductSorting(string &$query, string $sorting): void
    {
        switch ($sorting) {
            case Product::SORT_RANDOM:
            default:
                $query .= ' ORDER BY p.sequence ASC, p.id DESC';
                break;
            case Product::SORT_PRICE_ASC:
                $query .= ' ORDER BY p.price ASC';
                break;
            case Product::SORT_PRICE_DESC:
                $query .= ' ORDER BY p.price DESC';
                break;
            case Product::SORT_CREATED_AT:
                $query .= ' ORDER BY p.created_on DESC';
                break;
        }
    }

    /**
     * Build the search query
     *
     * @param string $alias
     * @param string $sql
     * @param string $searchTerm
     * @param array $parameters
     */
    private function buildSearchQuery(string $alias, string &$sql, string $searchTerm, array &$parameters): void
    {
        $sql .= ' (';
        $sql .= $alias . '.title LIKE :search_term OR ';
        $sql .= $alias . '.summary LIKE :search_term OR ';
        $sql .= $alias . '.text LIKE :search_term OR ';
        $sql .= $alias . '.sku LIKE :search_term';
        $sql .= ') ';

        $parameters['search_term'] = '%' . $searchTerm . '%';
    }

    /**
     * Get all active products limited per page
     *
     * @param Locale $locale
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function findProductsPerPage(Locale $locale, $page = 1, $limit = 100)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->where('i.locale = :locale')
            ->andWhere('i.hidden = :hidden')
            ->setParameters([
                'locale' => $locale,
                'hidden' => false,
            ])
            ->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getResult();

    }

    /**
     * Count the products
     *
     * @param Locale $locale
     *
     * @return integer
     * @throws NonUniqueResultException
     * @throws NoResultException
     *
     */
    public function getActiveCount(Locale $locale): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return $query_builder->select('COUNT(i.id)')
            ->where('i.locale = :locale')
            ->andWhere('i.hidden = :hidden')
            ->setParameters([
                'locale' => $locale,
                'hidden' => false,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
