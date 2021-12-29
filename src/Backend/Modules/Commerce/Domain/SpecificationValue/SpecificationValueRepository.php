<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Backend\Modules\Commerce\Domain\SpecificationValue\Exception\SpecificationValueNotFound;
use Common\Doctrine\Entity\Meta;
use Common\Uri;
use Doctrine\ORM\EntityRepository;

class SpecificationValueRepository extends EntityRepository
{
    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function add(SpecificationValue $product): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($product);
    }

    /**
     * @throws SpecificationValueNotFound
     */
    public function findOneById(?int $id): ?SpecificationValue
    {
        if ($id === null) {
            throw SpecificationValueNotFound::forEmptyId();
        }

        /** @var SpecificationValue $product */
        $entity = $this->findOneBy(['id' => $id]);

        if ($entity === null) {
            throw SpecificationValueNotFound::forId($id);
        }

        return $entity;
    }

    public function removeById($id): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        array_map(
            function (SpecificationValue $entity) {
                $this->getEntityManager()->remove($entity);
            },
            (array) $this->findBy(['id' => $id])
        );
    }

    public function findForAutoComplete(
        string $query,
        int $specificationId,
        ?int $page_limit = 10,
        ?int $page = 1
    ) {
        $queryBuilder = $this->createQueryBuilder('i');

        $queryBuilder->join(Specification::class, 's', 'WITH', 's.id = i.specification')
                     ->where(
                         $queryBuilder->expr()->orX(
                             $queryBuilder->expr()->like('i.value', ':query')
                         )
                     )
                     ->andWhere('s.id = :specification_id')
                     ->setParameter('query', '%' . $query . '%')
                     ->setParameter('specification_id', $specificationId);

        return $queryBuilder->getQuery()
                            ->setMaxResults($page_limit)
                            ->setFirstResult(($page - 1) * $page_limit)
                            ->getResult();
    }

    /**
     * @param string $url
     * @param int    $specification
     * @param int    $id
     *
     * @return string
     */
    public function getUrl($url, $specification, $id)
    {
        $url = Uri::getUrl((string) $url);
        $query_builder = $this->createQueryBuilder('i');
        $query_builder->join(Meta::class, 'm', 'WITH', 'm = i.meta')
                      ->where($query_builder->expr()->eq('m.url', ':url'))
                      ->andWhere('i.specification = :specification')
                      ->setParameters(
                          [
                              'url' => $url,
                              'specification' => $specification,
                          ]
                      );

        if ($id !== null) {
            $query_builder->andWhere($query_builder->expr()->neq('i.id', ':id'))
                          ->setParameter('id', $id);
        }

        if (count($query_builder->getQuery()->getResult())) {
            $url = Model::addNumber($url);

            return self::getURL($url, $specification, $id);
        }

        return $url;
    }
}
