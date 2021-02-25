<?php

namespace Backend\Modules\Commerce\Domain\Category;

use Backend\Core\Engine\Model;
use Common\Doctrine\ValueObject\AbstractImage;
use ForkCMS\Utility\Thumbnails;

final class Image extends AbstractImage
{
    /**
     * @return string
     */
    protected function getUploadDir(): string
    {
        return 'Commerce/categories';
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

    /**
     * {@inheritdoc}
     */
    public function getWebPath(string $subDirectory = null): string
    {
        // Generate thumbnails when required
        if (!file_exists($this->getAbsolutePath($subDirectory) ) && preg_match('/^([0-9]+)x([0-9]+)$/', $subDirectory)) {
            if (!is_dir($this->getUploadRootDir($subDirectory))) {
                mkdir($this->getUploadRootDir($subDirectory));
            }

            if (!file_exists($this->getAbsolutePath('source'))) {
                return '';
            }

            try {
                Model::get(Thumbnails::class)->generate(
                    FRONTEND_FILES_PATH . '/' . $this->getTrimmedUploadDir(),
                    $this->getAbsolutePath('source')
                );
            } catch (\SpoonThumbnailException $e) {
                return parent::getWebPath($subDirectory);
            }
        }

        return parent::getWebPath($subDirectory);
    }
}
