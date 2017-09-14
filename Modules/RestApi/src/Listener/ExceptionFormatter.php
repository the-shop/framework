<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\Exception\MethodNotAllowedException;
use Framework\Base\Application\Exception\NotFoundException;
use Framework\Base\Application\Exception\ValidationException;
use Framework\Base\Event\ListenerInterface;
use Framework\Http\Response\Response;

/**
 * Class ExceptionFormatter
 * @package Framework\RestApi\Listener
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
        $errors = [];
        $response = new Response();

        if ($exception instanceof \RuntimeException === true) {
            $response->setCode(500);
            $errors = [
                $exception->getMessage()
            ];
        }

        if ($exception instanceof \Exception === true) {
            $response->setCode(500);
            $errors = [
                $exception->getMessage()
            ];
        }

        if ($exception instanceof NotFoundException === true) {
            $response->setCode(404);
            $errors = [
                $exception->getMessage()
            ];
        }

        if ($exception instanceof MethodNotAllowedException === true) {
            $response->setCode(403);
            $errors = [
                $exception->getMessage()
            ];
        }

        if ($exception instanceof ValidationException === true) {
            /**
             * @var ValidationException $exception
             */
            $errors = $exception->getFailedValidations();
            $response->setCode(400);
        }

        $response->setBody([
            'error' => true,
            'errors' => $errors
        ]);

        $this->getApplication()
            ->setResponse($response);

        return $this;
    }
}
