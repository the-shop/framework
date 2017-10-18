<?php

namespace Framework\Terminal\Commands;

use Framework\Terminal\Commands\Cron\Schedule;

/**
 * Class CronJob
 * @package Framework\Base\Terminal\Commands\Cron
 */
class CronJob extends Schedule
{
    /**
     * All cron jobs should be registered (added) in this method.
     * At the end method runs all cron jobs.
     * You can add cron job with:
     * -----------------------------------------------------------------
     *                  $this->addCronJob(
     *                      string $commandName,
     *                      string $timeExpression,
     *                      array $parameters = []
     *                      );
     * -----------------------------------------------------------------
     * @return array
     */
    public function handle()
    {
        /*--------------------------- REGISTER CRON JOBS HERE ------------------------*/

        /*----------------------------------------------------------------------------*/

        return $this->runCronJobs();
    }
}
