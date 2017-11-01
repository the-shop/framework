<?php

namespace Application\Services;

use Aws\S3\S3Client;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\ServiceInterface;
use Framework\Base\FileUpload\FileUploader;

/**
 * Class FileUploadService
 * @package Application\Services
 */
class FileUploadService implements ServiceInterface
{
    use ApplicationAwareTrait;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return self::class;
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @return string
     */
    public function uploadFile(string $filePath, string $fileName)
    {
        $app = $this->getApplication();
        $appConfiguration = $app
            ->getConfiguration();

        $fileUploadClientPath = $appConfiguration
            ->getPathValue('servicesConfig.' . self::class . '.fileUploadClient.classPath');

        $constructorArguments = $appConfiguration->getPathValue(
            'servicesConfig.'
            . self::class
            . '.fileUploadClient.constructorArguments'
        );

        /**
         * @var S3Client $fileUploadClient
         */
        $fileUploadClient = new $fileUploadClientPath($constructorArguments);

        $fileUploadInterface = $appConfiguration
            ->getPathValue('servicesConfig.' . self::class . '.fileUploadInterface');

        $fileUploadInterface = new $fileUploadInterface();

        $fileUploader = new FileUploader($app, $fileUploadInterface);
        $fileUploader->setClient($fileUploadClient);

        return $fileUploader->uploadFile($filePath, $fileName);
    }
}
