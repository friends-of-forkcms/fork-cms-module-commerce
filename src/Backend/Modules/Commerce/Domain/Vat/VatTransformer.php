<?php

namespace Backend\Modules\Commerce\Domain\Vat;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

class VatTransformer implements DataTransformerInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($vatId)
    {
        if (null === $vatId) {
            return '';
        }

        return $this->entityManager
            ->getRepository(Vat::class)
            ->find($vatId);
    }

    public function reverseTransform($vat)
    {
        if (!$vat) {
            return null;
        }

        return $vat->getId();
    }
}
