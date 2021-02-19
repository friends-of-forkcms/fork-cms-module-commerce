<?php

namespace Backend\Modules\Catalog\Domain\OrderAddress;

use Backend\Modules\Catalog\Domain\Account\Account;
use Backend\Modules\Catalog\Domain\OrderAddress\Exception\OrderAddressNotFound;
use Backend\Modules\Catalog\Domain\Product\Product;
use Common\Locale;
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
     * @param int $id
     * @param Account $account
     * @return OrderAddress
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
