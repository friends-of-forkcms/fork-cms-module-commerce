<?php

namespace Backend\Modules\Commerce\Domain\Product;

use Common\Doctrine\ValueObject\AbstractImage;

final class Image extends AbstractImage
{
    protected function getUploadDir(): string
    {
        return 'Commerce/products';
    }

    /**
     * @see AbstractImage::prepareToUpload()
     */
    public function prepareToUpload(): void
    {
        if ($this->getFile()) {
            $this->namePrefix = str_replace(
                '.'.$this->getFile()->getClientOriginalExtension(),
                '',
                $this->getFile()->getClientOriginalName()
            );
        }

        parent::prepareToUpload();
    }
}
