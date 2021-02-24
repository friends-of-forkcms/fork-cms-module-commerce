<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\OrderAddress\Exception\OrderAddressNotFound;
use Doctrine\ORM\EntityRepository;

class OrderAddressRepository extends EntityRepository
{
    public function add(OrderAddress $orderAddress): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->persist($orderAddress);
    }

    public function remove(OrderAddress $orderAddress): void
    {
        $this->getEntityManager()->remove($orderAddress);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws OrderAddressNotFound
     */
    public function findByIdAndAccount(int $id, Account $account): OrderAddress
    {
        if ($id === null) {
            throw OrderAddressNotFound::forEmptyId();
        }

        $address = $this->findOneBy(['id' => $id, 'account' => $account]);

        if ($address === null) {
            throw OrderAddressNotFound::forId($id);
        }

        return $address;
    }
}
