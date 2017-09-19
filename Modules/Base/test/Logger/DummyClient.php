<?php

namespace Framework\Base\Test\Logger;

/**
 * Class DummyClient
 * @package Framework\RestApiTest\Logger
 */
class DummyClient
{
    /**
     * @var string
     */
    private $dsn;

    /**
     * DummyClient constructor.
     * @param string $dsn
     */
    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * @return $this
     */
    public function install()
    {
        return $this;
    }

    /**
     * @param $message
     * @param array $params
     * @param array $data
     * @return array
     */
    public function captureMessage($message, $params = [], $data = [])
    {
        return [
            'message' => $message,
            'params' => $params,
            'data' => $data
        ];
    }

    /**
     * @param $exception
     * @param null $data
     * @return array
     */
    public function captureException($exception, $data = null)
    {
        return [
            'exception' => $exception,
            'data' => $data
        ];
    }
}
