<?php

namespace Application\CronJobs;

use Framework\Terminal\Commands\CronJob;

class SlackSendMessage extends CronJob
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Load configuration
        $priorityMapping = Config::get('sharedSettings.internalConfiguration.slack.priorityToMinutesDelay');

        $sent = [];

        $now = new \DateTime();
        $unixNow = (int) $now->format('U');
        $currentMinuteUnix = $unixNow - $unixNow % 60; // First second of current minute
        $nextMinuteUnix = $currentMinuteUnix + 60; // First second of next minute

        // Load by priority
        foreach ($priorityMapping as $priority => $minutesDelay) {
            $query = GenericModel::whereTo('slackMessages')
                                 ->query();
            // Find messages in required priority
            $query->where('priority', '=', $priority)
                // Make sure we don't re-send things
                  ->where('sent', '=', false)
                // Check when it was added and make sure that required delay is within current minute
                  ->where('runAt', '<', $nextMinuteUnix);

            $messages = $query->get();

            foreach ($messages as $message) {
                SlackChat::message($message->recipient, $message->message);
                $message->sent = true;
                $message->save();
                $sent[] = $message->recipient;
            }
        }

        return $sent;

//
//        /*--------------------------- REGISTER CRON JOBS HERE ------------------------*/
//
//        $this->addCronJob(
//            'test',
//            $this->everyFiveMinutes()->getCronTimeExpression(),
//            [
//                'testParam' => 'test required param',
//                'testOptionalParam' => 'test optional param',
//            ]
//        );
//
//        /*-----------------------------------------------------------------------------*/
//
//        return $this->runCronJobs();
    }
}
