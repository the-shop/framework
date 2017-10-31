<?php
/**
 * Created by PhpStorm.
 * User: laptop02
 * Date: 31.10.17.
 * Time: 11:43
 */

namespace Framework\Base\FileUpload;

use Framework\Base\Application\ApplicationAwareTrait;

abstract class FileUpload implements FileUploadInterface
{
    use ApplicationAwareTrait;

    private $client = null;

    /**
     * @param $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }
}
