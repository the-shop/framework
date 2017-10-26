<?php

namespace Application\CronJobs;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Model\BrunoInterface;
use Framework\Terminal\Commands\Cron\CronJob;

/**
 * Class MonthlyMinimumCheck
 * @package Application\CronJobs
 */
class MonthlyMinimumCheck extends CronJob
{
    use ApplicationAwareTrait;

    /**
     * Execute the console command.
     */
    public function execute()
    {
        $app = $this->getApplication();
        $profiles = $app
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadMultiple();

        $admins = [];
        foreach ($profiles as $profile) {
            if ($profile->getAttribute('admin') === true) {
                $admins[] = $profile;
            }
        }

        foreach ($profiles as $profile) {
            $employee = $profile->getAttribute('employee');
            if (!$employee || $employee === false) {
                continue;
            }

            $profilePerformance = $app->getService('profilePerformance');
            $dateStart = new \DateTime();
            $unixStart = (int)$dateStart->modify('first day of last month')->format('U');

            $dateEnd = new \DateTime();
            $unixEnd = (int)$dateEnd->modify('last day of last month')->format('U');

            $performance =
                $profilePerformance->aggregateForTimeRange(
                    $profile,
                    $unixStart,
                    $unixEnd
                );

            $realPayoutCombined = $performance['realPayoutCombined'];
            $rolesDefinition = $app->getConfiguration()
                ->getPathValue('internal.employees.roles');

            $requiredMinimum =
                $rolesDefinition[$profile->getAttribute('employeeRole')]['minimumEarnings'];

            // Check if minimum missed
            if ($realPayoutCombined < $requiredMinimum) {
                // Update profile
                $profileMinimumsMissed = $profile->getAttribute('minimumsMissed');
                if (!$profileMinimumsMissed) {
                    $profileMinimumsMissed = 0;
                }
                $profileMinimumsMissed++;
                $profile->setAttribute('minimumsMissed', $profileMinimumsMissed);
                $profile->save();

                $profileAttributes = $profile->getAttributes();
                // Format messages
                $minimumDiff = $requiredMinimum - $realPayoutCombined;
                $userMessage = 'Hey, you\'ve just missed monthly minimum for your role by: *'
                    . $minimumDiff
                    . '*. Total monthly minimums missed: *'
                    . $profileAttributes['minimumsMissed']
                    . '*';

                $adminMessage = 'Hey, *' . $profileAttributes['name']
                    . '* (ID: '
                    . $profileAttributes['_id']
                    . ') missed their monthly minimum by *'
                    . $minimumDiff
                    . '*. Total monthly minimums missed: *'
                    . $profileAttributes['minimumsMissed']
                    . '*';

                // Notify employee
                if (array_key_exists('slack', $profileAttributes) === true
                    && empty($profileAttributes['slack']) === false
                    && $profileAttributes['active'] === true
                ) {
                    $recipient = '@' . $profileAttributes['slack'];
                   //TODO: implement after SlackService is implemented
                    // Slack::sendMessage($recipient, $userMessage, Slack::MEDIUM_PRIORITY);
                }

                /**
                 * @var BrunoInterface $admin
                 */
                // Notify admins
                foreach ($admins as $admin) {
                    $adminAttributes = $admin->getAttributes();
                    if (array_key_exists('slack', $adminAttributes) === true
                        && empty($adminAttributes['slack']) === false
                        && $adminAttributes['active'] === true
                    ) {
                        $recipient = '@' . $adminAttributes['slack'];
                        //TODO: implement after SlackService is implemented
                     //   Slack::sendMessage($recipient, $adminMessage, Slack::MEDIUM_PRIORITY);
                    }
                }
            }
        }
    }
}
