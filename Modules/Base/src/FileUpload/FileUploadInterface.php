<?php

namespace Framework\Base\FileUpload;

use Framework\Base\Application\ApplicationAwareInterface;

/**
 * Interface FileUploadInterface
 * @package Framework\Base\FileUpload
 */
interface FileUploadInterface extends ApplicationAwareInterface
{
    /**
     * @param $filePath
     * @param $fileName
     * @return mixed
     */
    public function uploadFile($filePath, $fileName);

    /**
     * @param $client
     * @return FileUploadInterface
     */
    public function setClient($client);

    /**
     * @return mixed
     */
    public function getClient();
}
