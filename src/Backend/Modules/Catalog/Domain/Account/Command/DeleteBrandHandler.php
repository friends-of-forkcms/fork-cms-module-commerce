<?php

namespace Backend\Modules\Catalog\Domain\Brand\Command;

use Backend\Core\Engine\Model;
use Backend\Modules\Catalog\Domain\Brand\BrandRepository;

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
