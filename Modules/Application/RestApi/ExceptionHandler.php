<?php

namespace Framework\Application\RestApi;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Http\Response\Response;

/**
 * Class ExceptionHandler
 * @package Framework\Application\RestApi
 */
class ExceptionHandler implements ApplicationAwareInterface
{
    /**
     * @const string
     */
    const EVENT_EXCEPTION_HANDLER_HANDLE_PRE = 'EVENT\EXCEPTION_HANDLER\HANDLE_PRE';

    /**
     * @const string
     */
    const EVENT_EXCEPTION_HANDLER_HANDLE_POST = 'EVENT\EXCEPTION_HANDLER\HANDLE_POST';

    use ApplicationAwareTrait;

    /**
     * @param \Exception $e
     * @return $this
     */
    public function handle(\Exception $e)
    {
        $application = $this->getApplication();

        $application->triggerEvent(self::EVENT_EXCEPTION_HANDLER_HANDLE_PRE, $e);

        // TODO: Additional handling?

        $application->triggerEvent(self::EVENT_EXCEPTION_HANDLER_HANDLE_POST, $e);

        return $this;
    }
}
