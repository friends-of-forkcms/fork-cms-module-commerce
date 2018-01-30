<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use Backend\Modules\Catalog\Domain\Cart\Exception\CartNotFound;
use Common\Locale;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Frontend\Core\Engine\Model as FrontendModel;

class CartRepository extends EntityRepository
{
    public function save(Cart $cart): void
    {
        $this->getEntityManager()->persist($cart);
        $this->getEntityManager()->flush();
    }

    public function findOneByIdAndLocale(?int $id, Locale $locale): ?Cart
    {
        if ($id === null) {
            throw CartNotFound::forEmptyId();
        }

        /** @var Cart $cart */
        $cart = $this->findOneBy(['id' => $id, 'locale' => $locale]);

        if ($cart === null) {
            throw CartNotFound::forId($id);
        }

        return $cart;
    }

    public function remove(Cart $cart): void
    {
        // We don't flush here, see http://disq.us/p/okjc6b
        $this->getEntityManager()->remove($cart);
    }

    public function getStatusCount(int $status): int
    {
        $query_builder = $this->createQueryBuilder('i');

        return $query_builder->select('COUNT(i.id) as cart_count')
                             ->andWhere('i.status = :status')
                             ->setParameter('status', $status)
                             ->getQuery()
                             ->getSingleScalarResult();
    }

    /**
     * Find by a cart by session id. When there is no cart found create a new one
     *
     * @param string $hash
     * @param string $ip
     *
     * @return Cart
     */
    public function findBySessionId(string $hash, string $ip)
    {
        $query_builder = $this->createQueryBuilder('i');

        try {
            $entity = $query_builder->where('i.session_id = :session_id')
                                    ->setParameter('session_id', $hash)
                                    ->getQuery()
                                    ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $entity = null;
        }

        if (!$entity) {
            $entity = new Cart();
            $entity->setSessionId($hash);
            $entity->setIp($ip);

            $this->save($entity);
        }

        return $entity;
    }

    /**
     * Get the active cart from the session
     *
     * @param bool $createNew
     *
     * @return Cart
     */
    public function getActiveCart(bool $createNew = true): ?Cart
    {
        $cookie  = FrontendModel::get('fork.cookie');
        $request = FrontendModel::getRequest();

        if ( ! $cartHash = $cookie->get('cart_hash')) {
            if ($createNew) {
                return new Cart();
            }

            return null;
        }

        return $this->findBySessionId($cartHash, $request->getClientIp());
    }
}
