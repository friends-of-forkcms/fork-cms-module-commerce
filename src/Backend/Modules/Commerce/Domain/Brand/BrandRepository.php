<?php

namespace Backend\Modules\Commerce\Domain\Brand;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Brand\Exception\BrandNotFound;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Common\Uri;
use Doctrine\ORM\EntityRepository;

class BrandRepository extends EntityRepository
{
    public function add(Brand $brand): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($brand);
    }

    public function findOneByIdAndLocale(?int $id, Locale $locale): ?Brand
    {
        if ($id === null) {
            throw BrandNotFound::forEmptyId();
        }

        /** @var Brand $brand */
        $brand = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($brand === null) {
            throw BrandNotFound::forId($id);
        }

        return $brand;
    }

    /**
     * @return Brand[]
     */
    public function findByLocale(Locale $locale)
    {
        /**
         * @var Brand[] $brands
         */
        $brands = $this->findBy(
            ['locale' => $locale],
            ['sequence' => 'ASC']
        );

        return $brands;
    }

    public function removeByIdAndLocale($id, Locale $locale): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (Brand $brand) {
                $this->getEntityManager()->remove($brand);
            },
            (array) $this->findBy(['id' => $id, 'locale' => $locale])
        );
    }

    /**
     * Get the next sequence in line.
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
}
