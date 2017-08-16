<?php

namespace Framework\Application\RestApi\Listener;

use Framework\Application\RestApi\NotFoundException;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Events\ListenerInterface;
use Framework\Http\Response\Response;

/**
 * Class ExceptionFormatter
 * @package Framework\Application\RestApi\Listener
 */
class ExceptionFormatter implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param \Exception $exception
     * @return $this
     */
    public function handle($exception)
    {
        $handledResponse = null;
        $response = new Response();

        if ($exception instanceof \RuntimeException) {
            $response->setHttpCode(500);
        }

        if ($exception instanceof NotFoundException) {
            $response->setHttpCode(404);
        }

        $response->setBody([
            'error' => true,
            'errors' => [$exception->getMessage()]
        ]);

        $this->getApplication()
            ->setResponse($response);

        return $this;
    }
}
