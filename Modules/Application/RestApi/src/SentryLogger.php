<?php

namespace Framework\Application\RestApi;

use Framework\Base\Logger\LoggerInterface;
use Framework\Base\Logger\LogInterface;

class SentryLogger implements LoggerInterface
{
    private $dsn = 'https://11d94f69523b40d381dcb6c580baab6c:635134caf25940c1af98ff74da4b3fe6@sentry.io/205061';

    private $client;

    public function __construct()
    {
        $this->client = new \Raven_Client($this->getDsn());
        $this->client->install();
    }

    public function getDsn()
    {
        return $this->dsn;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function log(LogInterface $log)
    {
        if ($log->isException()) {
            $event = $this->logException($log);
        } else {
            $event = $this->logMessage($log);
        }

        return $event;
    }

    private function logMessage(LogInterface $log)
    {
        $eventId = $this->getClient()
            ->captureMessage(
                $log->getPayload(),
                [],
                $log->getAllData()
            );

        return $eventId;
    }

    private function logException(LogInterface $log)
    {
        $eventId = $this->getClient()
            ->captureException(
                $log->getPayload(),
                $log->getAllData()
            );

        return $eventId;
    }
}
