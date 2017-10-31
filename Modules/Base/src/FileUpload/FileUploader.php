<?php

namespace Framework\Base\FileUpload;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\ApplicationInterface;

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
     * @param ApplicationInterface $app
     * @param FileUploadInterface $fileUploadInterface
     */
    public function __construct(
        ApplicationInterface $app,
        FileUploadInterface $fileUploadInterface
    ) {
        $this->fileUploadInterface = $fileUploadInterface;
        $this->fileUploadInterface->setApplication($app);
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
     * @return mixed
     */
    public function uploadFile($file, $fileName)
    {
        return $this->fileUploadInterface->uploadFile($file, $fileName);
    }
}
