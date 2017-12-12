<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

class OrderStatusTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object to a string.
     *
     * @param  mixed $orderStatusId
     * @return string
     */
    public function transform($orderStatusId)
    {
        if (null === $orderStatusId) {
            return '';
        }

        $orderStatus = $this->entityManager
            ->getRepository(OrderStatus::class)
            // query for the issue with this id
            ->find($orderStatusId)
        ;

        return $orderStatus;
    }

    /**
     * Transforms a string to an object.
     *
     * @param string $orderStatus
     *
     * @return OrderStatus|null
     */
    public function reverseTransform($orderStatus)
    {
        if (!$orderStatus) {
            return;
        }

        return $orderStatus->getId();
    }
}
