<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Common\Uri;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use PDO;

class ProductRepository extends EntityRepository
{
    public function add(Product $product): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($product);
    }

    /**
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

    /** @return array<int,Product> */
    public function findActive(): array
    {
        /* @var Product $product */
        return $this->findBy(['hidden' => false]);
    }

    /** @return array<int,Product> */
    public function findActiveByIds(array $ids): array
    {
        return $this->findBy(['id' => $ids, 'hidden' => false]);
    }

    /**
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
        $items = (array) $this->findBy(['id' => $id, 'locale' => $locale]);
        foreach ($items as $item) {
            $this->getEntityManager()->remove($item);
        }
    }

    /**
     * Find a product by category and product url.
     *
     * @param string $url
     *
     * @throws NonUniqueResultException
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
     * Count the products.
     *
     * @throws NonUniqueResultException
     */
    public function getCount(Locale $locale): int
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->select('COUNT(i.id)')
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

    /**
     * Find a product based on the url part.
     *
     * @throws NonUniqueResultException
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
                    'url' => $url,
                ]
            )
            ->getQuery()
            ->getSingleResult();
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
        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder->join(Meta::class, 'm', 'WITH', 'm = i.meta')
            ->where($queryBuilder->expr()->eq('m.url', ':url'))
            ->andWhere($queryBuilder->expr()->eq('i.locale', ':locale'))
            ->setParameters(
                [
                    'url' => $url,
                    'locale' => $locale,
                ]
            );

        if ($id !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->neq('i.id', ':id'))
                ->setParameter('id', $id);
        }

        if (count($queryBuilder->getQuery()->getResult())) {
            $url = Model::addNumber($url);

            return self::getURL($url, $locale, $id);
        }

        return $url;
    }

    /**
     * Find the products limited by category.
     *
     * @return Product[]
     */
    public function findLimitedByCategory(Category $category, int $limit, int $offset = 0, string $sorting = Product::SORT_STANDARD)
    {
        $sql = 'SELECT p.* FROM commerce_products p WHERE p.categoryId = :category AND p.hidden = :hidden';
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
     * Filter the products based on specification values.
     */
    public function filterProducts(array $filters, Category $category, int $limit, int $offset, string $sorting): array
    {
        $sql = 'SELECT p.* FROM commerce_products p WHERE p.categoryId = :category AND p.hidden = :hidden';
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
     * Filter the products and count the result.
     */
    public function filterProductsCount(array $filters, Category $category): int
    {
        $sql = 'SELECT count(p.id) as total_products FROM commerce_products p WHERE p.categoryId = :category AND p.hidden = :hidden';
        $parameters = [
            'category' => $category->getId(),
            'hidden' => false,
        ];

        $this->buildFilterQuery($sql, $parameters, $filters);

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($parameters);

        return (int) $stmt->fetchColumn(0);
    }

    /**
     * Filter the products based on specification values and search string.
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
     * Filter and search the products and count the result.
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

        return (int) $stmt->fetchColumn(0);
    }

    /**
     * Search the products by the given search string.
     *
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
     * Count the products.
     */
    public function getSearchProductCount(string $searchTerm, Locale $locale): int
    {
        $sql = 'SELECT p.* FROM commerce_products p WHERE ';
        $parameters = [];

        $this->buildSearchQuery('p', $sql, $searchTerm, $parameters);

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($parameters);

        return (int) $stmt->fetchColumn(0);
    }

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
                ++$j;
            }

            $sql .= ' AND p.id IN(
                SELECT psv.productId
                FROM commerce_specification_values sv
                INNER JOIN commerce_products_specification_values psv ON psv.specificationValueId = sv.id
                INNER JOIN meta svmeta ON svmeta.id = sv.metaId
                INNER JOIN commerce_specifications s ON s.id = sv.specificationId
                INNER JOIN meta smeta ON smeta.id = s.metaId
                WHERE s.filter = 1
                AND smeta.url = :specification' . $i . '
                AND svmeta.url IN (' . implode(', ', $specificationValuesPlaceholder) . ')
            )';

            // Update the counter
            ++$i;
        }
    }

    private function setProductSorting(string &$query, string $sorting): void
    {
        switch ($sorting) {
            case Product::SORT_STANDARD:
            default:
                $query .= ' ORDER BY p.sequence ASC, p.id DESC';

                break;
            case Product::SORT_PRICE_ASC:
                $query .= ' ORDER BY p.priceAmount ASC';

                break;
            case Product::SORT_PRICE_DESC:
                $query .= ' ORDER BY p.priceAmount DESC';

                break;
            case Product::SORT_CREATED_AT:
                $query .= ' ORDER BY p.createdAt DESC';

                break;
        }
    }

    /**
     * Build the search query.
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
     * Get all active products limited per page.
     *
     * @param int $page
     * @param int $limit
     *
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
     * Count the products.
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getActiveCount(Locale $locale): int
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->select('COUNT(i.id)')
            ->where('i.locale = :locale')
            ->andWhere('i.hidden = :hidden')
            ->setParameters([
                'locale' => $locale,
                'hidden' => false,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $limit
     * @param Locale $locale
     * @return Product[]
     */
    public function getMostRecent(int $limit, Locale $locale): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.locale = :locale')
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setParameters(['locale' => $locale])
            ->getQuery()
            ->getResult();
    }

    /**
     * After searching the search_index, we need to decorate the search results and return the search objects.
     * We use a raw query for performance reasons.
     * @param int[] $ids
     * @return Product[]
     */
    public function search(array $ids): array
    {
        /** @var EntityManager $em */
        $em = FrontendModel::getContainer()->get('doctrine.orm.entity_manager');
        $items = $em->getConnection()->executeQuery(
            '
            SELECT
                p.id,
                p.title,
                p.categoryId AS category_id,
                p.text,
                p.priceAmount AS price_amount,
                m.url AS url,
                CONCAT(:detailUrl, "/", m2.url, "/", m.url) AS full_url,
                MIN(mgmi.mediaItemId) AS media_item_id
            FROM commerce_products AS p
            INNER JOIN meta AS m ON m.id = p.metaId
            LEFT JOIN commerce_categories AS cc ON cc.id = p.categoryId
            LEFT JOIN meta AS m2 ON m2.id = cc.metaId
            LEFT JOIN MediaGroupMediaItem AS mgmi ON mgmi.mediaGroupId = p.imageGroupId AND mgmi.sequence = 0
            WHERE
                p.hidden = 0
                AND p.id IN (:ids)
            GROUP BY 1',
            ['detailUrl' => FrontendNavigation::getUrlForBlock('Commerce'), 'ids' => $ids],
            ['detailUrl' => PDO::PARAM_STR, 'ids' => Connection::PARAM_INT_ARRAY]
        )->fetchAllAssociativeIndexed();

        // Fetch image preview thumb
        foreach ($items as &$item) {
            if (!empty($item['media_item_id'])) {
                $item['preview_image_url'] = FrontendModel::get('media_library.repository.item')
                    ->findOneById($item['media_item_id'])
                    ->getThumbnail('product_slider_thumbnail');
            }
        }

        //        // Fetch categories
        //        $categories = $this->getEntityManager()
        //            ->getRepository('commerce.repository.category')
        //            ->findBy(['id' => array_map(fn ($item) => $item['category_id'], $items)]);

        // Note: array must have the ID as key, else search breaks!
        return $items;
    }
}
