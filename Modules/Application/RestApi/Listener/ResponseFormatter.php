<?php

namespace Framework\Application\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Events\ListenerInterface;
use Framework\Base\Model\BrunoInterface;

/**
 * Class ResponseFormatter
 * @package Framework\Application\RestApi\Listener
 */
class ResponseFormatter implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param $payload
     * @return mixed
     */
    public function handle($payload)
    {
        $responseBody = $this->getApplication()
            ->getResponse()
            ->getBody();

        $out = $responseBody;

        if (is_array($responseBody) === true) {
            $out = [];
            foreach ($responseBody as $key => $responseItem) {
                $out[$key] = $this->formatSingleRecord($responseItem);
            }
        }

        $this->getApplication()
            ->getResponse()
            ->setBody($out);

        return $this;
    }

    public function formatSingleRecord($record)
    {
        $formatted = $record;
        if (is_array($record)) {
            foreach ($record as $key => $value) {
                $formatted[$key] = $this->formatSingleRecord($value);
            }
        }

        if ($record instanceof BrunoInterface) {
            $formatted = $this->formatSingleRecord($record->getAttributes());
        }

        return $formatted;
    }
}
