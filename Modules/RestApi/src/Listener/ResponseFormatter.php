<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Model\BrunoInterface;

/**
 * Class ResponseFormatter
 * @package Framework\RestApi\Listener
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
            $data = [];
            foreach ($responseBody as $key => $responseItem) {
                $data[$key] = $this->formatSingleRecord($responseItem);
            }
            $out = [
                'data' => $data,
                'pagination' => [] // TODO:
            ];
        } elseif ($responseBody instanceof BrunoInterface) {
            $data = $this->formatSingleRecord($responseBody);
            $out = [
                'data' => $data,
            ];
        }

        $this->getApplication()
            ->getResponse()
            ->setBody($out);

        return $this;
    }

    /**
     * @param $record
     * @return mixed
     */
    public function formatSingleRecord($record)
    {
        $formatted = $record;
        if (is_array($record)) {
            foreach ($record as $key => $value) {
                $formatted[$key] = $this->formatSingleRecord($value);
            }
        }

        if ($record instanceof BrunoInterface) {
            $modelAttributes = $record->getAttributes();

            $definedModelAttributes =
                $this->getApplication()
                    ->getRepositoryManager()
                    ->getRegisteredModelFields($record->getCollection());

            foreach ($definedModelAttributes as $attribute => $options) {
                if (array_key_exists($attribute, $modelAttributes) === false) {
                    $modelAttributes[$attribute] = null;
                }
            }

            $formatted = $this->formatSingleRecord($modelAttributes);
        }

        return $formatted;
    }
}
