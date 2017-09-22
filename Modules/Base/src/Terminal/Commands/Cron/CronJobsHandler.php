<?php

namespace Framework\Base\Terminal\Commands\Cron;

use DateTime;
use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class CronJobsHandler
 * @package Framework\Base\Terminal\Commands\Cron
 */
class CronJobsHandler implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @var array
     */
    protected $registeredJobs = [];

    /**
     * @return array
     */
    public function getRegisteredJobs()
    {
        return $this->registeredJobs;
    }

    /**
     * @param string $commandName
     * @param string $timeExpression
     * @param array $parameters
     * @return $this
     */
    public function addCronJob(
        string $commandName,
        string $timeExpression,
        array $parameters = []
    ) {
        $routes = $this->getApplication()->getDispatcher()->getRoutes();

        // Let's check if command is registered
        if (array_key_exists($commandName, $routes) === false) {
            throw new \InvalidArgumentException(
                'Command name ' . $commandName . ' is not registered.',
                404
            );
        }

        // Let's check if there are enough required params passed
        if (count($parameters) < count($routes[$commandName]['requiredParams'])) {
            throw new \InvalidArgumentException(
                'Not enough requiredParams passed for ' . $commandName . ' command',
                403
            );
        }

        // Add cron job to registeredJobs
        $this->registeredJobs[] = [
            'commandName' => $commandName,
            'handler' => $routes[$commandName]['handler'],
            'timeExpression' => $timeExpression,
            'parameters' => $parameters,
        ];

        return $this;
    }

    /**
     * Run registered cron jobs
     * @return array
     */
    public function runCronJobs()
    {
        $outPutMessages = [];
        $cronJobs = $this->getRegisteredJobs();
        $currentTime = (new DateTime())->format('Y-m-d H:i:s');

        foreach ($cronJobs as $job) {
            if ($this->parseCronExpression($currentTime, $job['timeExpression']) === true) {
                $handler = new $job['handler']();
                $handler->setApplication($this->getApplication());

                $parametersValues = array_values($job['parameters']);
                $output = $handler->handle(...$parametersValues);

                $outPutMessages[$job['commandName']] = [
                    'COMMAND DONE! STATUS CODE 200.',
                    'Response: ' => $output,
                ];
            }
        }

        return $outPutMessages;
    }

    /**
     * Parse cron expression and compare it to current time return true if cron job needs to run
     * or return false if cron job expression doesn't match current time
     * @param $currentTime
     * @param $cronTab
     * @return mixed
     */
    private function parseCronExpression($currentTime, $cronTab)
    {
        // Get current minute, hour, day, month, weekday
        $currentTime = explode(' ', date('i G j n w', strtotime($currentTime)));
        // Split crontab by space
        $cronTab = explode(' ', $cronTab);
        // Foreach part of cronTab
        foreach ($cronTab as $k => &$v) {
            // Remove leading zeros to prevent octal comparison, but not if number is already 1 digit
            $currentTime[$k] = preg_replace('/^0+(?=\d)/', '', $currentTime[$k]);
            // 5,10,15 each treated as separate parts
            $v = explode(',', $v);
            // Foreach part we now have
            foreach ($v as &$v1) {
                // Do preg_replace with regular expression to create evaluations from cronTab
                $v1 = preg_replace(
                    // Regex
                    [
                        // *
                        '/^\*$/',
                        // 5
                        '/^\d+$/',
                        // 5-10
                        '/^(\d+)\-(\d+)$/',
                        // */5
                        '/^\*\/(\d+)$/',
                    ],
                    // Evaluations
                    // trim leading 0 to prevent octal comparison
                    [
                        // * is always true
                        'true',
                        // Check if it is currently that time,
                        $currentTime[$k] . '===\0',
                        // Find if more than or equal lowest and lower or equal than highest
                        '(\1<=' . $currentTime[$k] . ' and ' . $currentTime[$k] . '<=\2)',
                        // Use modulus to find if true
                        $currentTime[$k] . '%\1===0',
                    ],
                    // Subject we are working with
                    $v1
                );
            }
            // Join 5,10,15 with `or` conditional
            $v = '(' . implode(' or ', $v) . ')';
        }
        // Require each part is true with `and` conditional
        $cronTab = implode(' and ', $cronTab);

        // Evaluate total condition to find if true
        return eval('return ' . $cronTab . ';');
    }
}
