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
     * This function should be called for the life cycle events PostPersist() and PostUpdate()
     */
    public function upload(): void
    {
        if (!$this->hasFile()) {
            return;
        }

        // check if we have an old image
        if ($this->oldFileName !== null) {
            $this->removeOldFile();
        }

        if (!$this->hasFile()) {
            return;
        }

        $this->writeFileToDisk();

        $this->file = null;
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
