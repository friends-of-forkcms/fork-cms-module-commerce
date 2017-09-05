<?php

namespace Backend\Modules\Catalog\Domain\Specification;

use Backend\Modules\Catalog\Domain\Specification\Exception\SpecificationNotFound;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Common\Uri;
use Doctrine\ORM\EntityRepository;
use Backend\Core\Engine\Model;

class SpecificationRepository extends EntityRepository
{
    public function add(Specification $specification): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($specification);
    }

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
