<?php

namespace Backend\Modules\Catalog\Domain\Category;

use Common\Doctrine\ValueObject\AbstractImage;

final class Image extends AbstractImage
{
    /**
     * @return string
     */
    protected function getUploadDir(): string
    {
        return 'Catalog/categories';
    }

    /**
     * @see AbstractImage::prepareToUpload()
     */
    public function prepareToUpload(): void
    {
        if ($this->getFile()) {
            $this->namePrefix = str_replace(
                '.' . $this->getFile()->getClientOriginalExtension(),
                '',
                $this->getFile()->getClientOriginalName()
            );
        }

        parent::prepareToUpload();
    }
}
