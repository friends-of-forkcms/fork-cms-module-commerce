<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Backend\Modules\Commerce\Domain\Account\Exception\AccountNotFound;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Frontend\Modules\Profiles\Engine\Profile;

class AccountRepository extends EntityRepository
{
    public function add(Account $account): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($account);
    }

    /**
     * @param Profile $profile
     * @return Account|null
     * @throws AccountNotFound
     */
    public function findOneByProfile(Profile $profile): ?Account
    {
        if ($profile->getId() === null) {
            throw AccountNotFound::forEmptyId();
        }

        /** @var Account $account */
        $account = $this->findOneBy(['profile_id' => $profile->getId()]);

        if ($account === null) {
            throw AccountNotFound::forId($profile->getId());
        }

        return $account;
    }

    /**
     * Get all accounts limited per page
     *
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function findAccountsPerPage($page = 1, $limit = 100)
    {
        $queryBuilder = $this->createQueryBuilder('i');

        return $queryBuilder->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getResult();
    }

    /**
     * Count the accounts
     *
     * @return integer
     * @throws NonUniqueResultException
     * @throws NoResultException
     *
     */
    public function getCount(): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return $query_builder->select('COUNT(i.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
