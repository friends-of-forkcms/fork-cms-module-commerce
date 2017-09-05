<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Product\Exception\ProductNotFound;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Common\Uri;
use Doctrine\ORM\EntityRepository;
use Backend\Core\Engine\Model;

class ProductRepository extends EntityRepository
{
    public function add(Product $product): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($product);
    }

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
     * @param array $sorting
     *
     * @return Product[]
     */
    public function findLimitedByCategory(Category $category, int $limit, int $offset = 0, ?array $sorting)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        $query = $queryBuilder->where('i.category = :category')
                              ->setParameter('category', $category)
                              ->orderBy('i.createdOn', 'ASC')
                              ->addOrderBy('i.id', 'DESC');

        if ($sorting) {

        }


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
        return $query_builder->getQuery()->getSingleScalarResult() + 1;
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
                             ->getQuery()->getSingleScalarResult();
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
}
