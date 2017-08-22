<?php

namespace Framework\Base\Application\Exception;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class ExceptionHandler
 * @package Framework\RestApi
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
