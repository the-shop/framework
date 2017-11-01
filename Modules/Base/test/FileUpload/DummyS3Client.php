<?php

namespace Framework\Base\Test\FileUpload;

/**
 * Class DummySendGridClient
 * @package Framework\Base\Test\Mailer
 */
class DummyS3Client
{
    /**
     * @var array
     */
    private $arguments = [];

    /**
     * DummyS3Client constructor.
     * @param array $arguments
     */
    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    /**
     * @param array $args
     * @return DummyS3Response
     */
    public function putObject(array $args = [])
    {
        return new DummyS3Response();
    }
}
