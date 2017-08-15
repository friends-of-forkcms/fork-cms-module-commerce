<?php

namespace Backend\Modules\Catalog\Domain\Product;

use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Product\Exception\ProductNotFound;
use Common\Doctrine\Entity\Meta;
use Backend\Modules\ContentBlocks\Domain\ContentBlock\Exception\ContentBlockNotFound;
use Common\Locale;
use Common\Uri;
use Doctrine\ORM\EntityRepository;
use Backend\Core\Engine\Model;
use League\Flysystem\Adapter\Local;

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
            throw ContentBlockNotFound::forEmptyId();
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
     * Find the products limited by values
     *
     * @param Locale $locale
     * @param integer $limit
     * @param integer $offset
     *
     * @return Product[]
     */
    public function findLimited(Locale $locale, int $limit, int $offset = 0)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->where('i.locale = :locale')
                            ->setParameter('locale', $locale)
                            ->orderBy('i.createdOn', 'ASC')
                            ->addOrderBy('i.id', 'DESC')
                            ->setMaxResults($limit)
                            ->setFirstResult($offset)
                            ->getQuery()
                            ->getResult();
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

    /**
     * Get an tree of products
     *
     * @param Locale $locale
     *
     * @return array
     */
    public function getTree(Locale $locale)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        /**
         * @var Product[] $query_result
         */
        $queryResult = $queryBuilder->where('i.locale = :locale')
                                    ->andWhere('i.parent IS NULL')
                                    ->setParameter('locale', $locale)
                                    ->orderBy('i.sequence', 'asc')
                                    ->getQuery()
                                    ->getResult();

        $treeResult = [];

        $this->parseTreeChildren($treeResult, $queryResult, 0);

        return $treeResult;
    }

    /**
     * A recursive function to populate the tree array
     *
     * @param array $treeResult
     * @param Product[] $products
     * @param integer $path
     *
     * @return void
     */
    private function parseTreeChildren(array &$treeResult, $products, int $path)
    {
        foreach ($products as $product) {
            $product->path                    = $path;
            $treeResult[$product->getTitle()] = $product;

            if ( ! $product->getChildren()->isEmpty()) {
                $this->parseTreeChildren($treeResult, $product->getChildren(), $path + 1);
            }
        }
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
