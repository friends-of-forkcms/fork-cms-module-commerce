<?php

namespace Backend\Modules\Commerce\Domain\Category;

use Backend\Modules\Commerce\Domain\Category\Exception\CategoryNotFound;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Common\Uri;
use Doctrine\ORM\EntityRepository;
use Backend\Core\Engine\Model;

class CategoryRepository extends EntityRepository
{
    public function add(Category $category): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($category);
    }

    /**
     * @param int|null $id
     * @param Locale $locale
     *
     * @return Category|null
     * @throws CategoryNotFound
     */
    public function findOneByIdAndLocale(?int $id, Locale $locale): ?Category
    {
        if ($id === null) {
            throw CategoryNotFound::forEmptyId();
        }

        /** @var Category $category */
        $category = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($category === null) {
            throw CategoryNotFound::forId($id);
        }

        return $category;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (Category $category) {
                $this->getEntityManager()->remove($category);
            },
            (array)$this->findBy(['id' => $id, 'locale' => $locale])
        );
    }

    /**
     * Find parent categories ordered by sequence
     *
     * @param Locale $locale
     *
     * @return Category[]
     */
    public function findParents(Locale $locale)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->where('i.parent IS NULL')
                            ->andWhere('i.locale = :locale')
                            ->setParameter('locale', $locale)
                            ->orderBy('i.sequence', 'ASC')
                            ->getQuery()
                            ->getResult();
    }

    public function findByLocaleAndUrl(Locale $locale, string $url): ?Category
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
            ->getOneOrNullResult();
    }

    /**
     * Get the next sequence in line
     *
     * @param Locale $locale
     * @param Category $parent
     *
     * @return integer
     */
    public function getNextSequence(Locale $locale, Category $parent = null): int
    {
        $query_builder = $this->createQueryBuilder('i');
        $query_builder->select('MAX(i.sequence) as sequence')
                      ->where('i.locale = :locale');

        $query_builder->setParameter('locale', $locale);

        // Include the parent if is set
        if ($parent) {
            $query_builder->andWhere('i.parent = :parent');
            $query_builder->setParameter('parent', $parent);
        }

        // Return the new sequence
        return $query_builder->getQuery()->getSingleScalarResult() + 1;
    }

    /**
     * Get an tree of categories
     *
     * @param Locale $locale
     *
     * @return array
     */
    public function getTree(Locale $locale)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        /**
         * @var Category[] $query_result
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
     * @param Category[] $categories
     * @param integer $path
     *
     * @return void
     */
    private function parseTreeChildren(array &$treeResult, $categories, int $path)
    {
        foreach ($categories as $category) {
            $category->path                    = $path;
            $treeResult[$category->getTitle()] = $category;

            if ( ! $category->getChildren()->isEmpty()) {
                $this->parseTreeChildren($treeResult, $category->getChildren(), $path + 1);
            }
        }
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
