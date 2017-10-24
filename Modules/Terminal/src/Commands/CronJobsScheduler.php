<?php

namespace Framework\Terminal\Commands;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Terminal\Commands\Cron\CronJobInterface;

/**
 * Class CronJob
 * @package Framework\Base\Terminal\Commands\Cron
 */
class CronJobsScheduler implements CommandHandlerInterface
{
    use ApplicationAwareTrait;

    /**
     * @return array
     */
    public function getRegisteredJobs(): array
    {
        return $this->getApplication()->getRegisteredCronJobs();
    }

    /**
     * Run registered cron jobs
     *
     * @return array
     */
    public function run(array $parameterValues = []): array
    {
        $outPutMessages = [];
        $cronJobs = $this->getRegisteredJobs();
        $currentTime = (new \DateTime())->format('Y-m-d H:i:s');

        foreach ($cronJobs as $job) {
            if ($this->parseCronExpression(
                $currentTime,
                $job->getCronTimeExpression()
            ) === true) {
                $job->setApplication($this->getApplication());
                $output = $job->execute();

                $outPutMessages[$job->getIdentifier()] = [
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
    private function parseCronExpression($currentTime, $cronTime)
    {
        // Get current minute, hour, day, month, weekday
        $currentTime = explode(' ', date('i G j n w', strtotime($currentTime)));
        // Split crontab by space
        $cronTime = explode(' ', $cronTime);
        // Foreach part of cronTab
        foreach ($cronTime as $k => &$v) {
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
        $cronTime = implode(' and ', $cronTime);

        // Evaluate total condition to find if true
        return eval('return ' . $cronTime . ';');
    }
}
