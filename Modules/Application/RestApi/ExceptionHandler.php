<?php

namespace Framework\Application\RestApi;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class ExceptionHandler
 * @package Framework\Application\RestApi
 */
class ExceptionHandler implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public function handle(\Exception $e)
    {
        $application = $this->getApplication();

        $application->triggerEvent('ExceptionHandler:handle:pre');

        if ($e instanceof NotFoundException) {
            return $e->getMessage();
        }

        if ($e instanceof \RuntimeException) {
            return 'Server error: ' . $e->getMessage();
        }

        $application->triggerEvent('ExceptionHandler:handle:post');
        throw $e;
    }
}
