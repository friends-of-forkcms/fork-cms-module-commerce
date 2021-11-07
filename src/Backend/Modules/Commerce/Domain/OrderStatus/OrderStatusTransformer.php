<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

class OrderStatusTransformer implements DataTransformerInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($orderStatusId)
    {
        if (null === $orderStatusId) {
            return '';
        }

        return $this->entityManager
            ->getRepository(OrderStatus::class)
            ->find($orderStatusId);
    }

    public function reverseTransform($orderStatus)
    {
        if (!$orderStatus) {
            return null;
        }

        return $orderStatus->getId();
    }
}
