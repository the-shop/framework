<?php

namespace Framework\Base\FileUpload;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class FileUploader
 * @package Framework\Base\FileUpload
 */
class FileUploader
{
    use ApplicationAwareTrait;

    /**
     * @var FileUploadInterface
     */
    private $fileUploadInterface;

    /**
     * FileUploader constructor.
     * @param FileUploadInterface $fileUploadInterface
     */
    public function __construct(FileUploadInterface $fileUploadInterface)
    {
        $this->fileUploadInterface = $fileUploadInterface;
        $this->fileUploadInterface->setApplication($this->getApplication());
    }

    /**
     * @param $client
     */
    public function setClient($client)
    {
        $this->fileUploadInterface->setClient($client);
    }

    /**
     * @param $file
     * @param $fileName
     */
    public function uploadFile($file, $fileName)
    {
        $this->fileUploadInterface->uploadFile($file, $fileName);
    }
}
