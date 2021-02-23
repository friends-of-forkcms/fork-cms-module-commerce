<?php

namespace Backend\Modules\Commerce\Domain\Brand\Command;

use Backend\Modules\Commerce\Domain\Brand\BrandRepository;

final class DeleteBrandHandler
{
    /** @var BrandRepository */
    private $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function handle(DeleteBrand $deleteBrand): void
    {
        $this->brandRepository->removeByIdAndLocale(
            $deleteBrand->brand->getId(),
            $deleteBrand->brand->getLocale()
        );
    }
}
