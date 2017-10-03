<?php

namespace Framework\Base\Terminal\Commands;

use Framework\Base\Terminal\Commands\Cron\Schedule;

/**
 * Class CronJob
 * @package Framework\Base\Terminal\Commands\Cron
 */
class CronJob extends Schedule
{
    /**
     * All cron jobs should be registered (added) in this method.
     * At the end method runs all cron jobs.
     * @return array
     */
    public function handle()
    {
        /*--------------------------- REGISTER CRON JOBS HERE ------------------------*/

        $this->addCronJob(
            'test',
            $this->everyFiveMinutes()->getCronTimeExpression(),
            [
                'testParam' => 'test required param',
                'testOptionalParam' => 'test optional param',
            ]
        );

        /*-----------------------------------------------------------------------------*/

        return $this->runCronJobs();
    }
}
