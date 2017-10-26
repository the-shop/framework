<?php

namespace Application\CronJobs;

use Application\Helpers\XpRecord;
use Framework\Base\Model\BrunoInterface;
use Framework\Terminal\Commands\Cron\CronJob;

/**
 * Class XpDeduction
 * @package Application\CronJobs
 */
class XpDeduction extends CronJob
{
    /**
     * Execute the console command.
     */
    public function execute()
    {
        $repoManager = $this->getApplication()->getRepositoryManager();
        $profiles = $repoManager->getRepositoryFromResourceName('users')
            ->loadMultiple();

        $profileHashMap = [];
        foreach ($profiles as $profile) {
            $profileHashMap[$profile->getAttribute('_id')] = $profile;
        }

        $daysChecked = 0;

        do {
            // Set current time of cron start and get all logs for previous 4 days
            $date = new \DateTime();
            $cronTime = $date->format('U');
            $unixNow = $date->format('U') - (24 * 60 * 60 * $daysChecked);
            $unixDayAgo = $unixNow - 24 * 60 * 60;
            $hexNow = dechex($unixNow);
            $hexDayAgo = dechex($unixDayAgo);
            $logsRepository = $repoManager->getRepositoryFromResourceName('logs');
            $query = $logsRepository->createNewQueryForModel($logsRepository->newModel());
            $query->addAndCondition('_id', '<', $hexNow . '0000000000000000')
                ->addAndCondition('_id', '>=', $hexDayAgo . '0000000000000000');

            $logs = $logsRepository->loadMultiple($query);

            $logHashMap = [];
            foreach ($logs as $log) {
                $logHashMap[$log->getAttribute('id')] = $log;
            }

            /**
             * @var BrunoInterface $user
             */
            foreach ($profileHashMap as $user) {
                $userAttributes = $user->getAttributes();
                if (isset($userAttributes['banned']) === true
                    && $userAttributes['banned'] === true
                ) {
                    unset($profileHashMap[$userAttributes['_id']]);
                    continue;
                }

                if (isset($userAttributes['active']) === true
                    && $userAttributes['active'] === false
                ) {
                    unset($profileHashMap[$userAttributes['_id']]);
                    continue;
                }

                if (array_key_exists($userAttributes['_id'], $logHashMap) === true) {
                    $profileHashMap[$userAttributes['_id']]->setAttribute(
                        'lastTimeActivityCheck',
                        $cronTime
                    );
                    $profileHashMap[$userAttributes['_id']]->save();
                    unset($profileHashMap[$userAttributes['_id']]);
                    continue;
                }

                if (array_key_exists($userAttributes['_id'], $logHashMap) === false
                    && $daysChecked === 4
                    && array_key_exists('role', $userAttributes)
                    && $userAttributes['role'] === 'standard') {
                    /**
                     * @var BrunoInterface $profile
                     */
                    $profile = $profileHashMap[$userAttributes['_id']];
                    $profileXp = $profile->getAttribute('xp');
                    if ($profileXp - 1 == 0) {
                        $profile->setAttribute('banned', true);
                    }

                    $userXp = (new XpRecord())
                        ->setApplication($this->getApplication())
                        ->getXpRecord($profile);

                    $records = $userXp->getAttribute('records');
                    $records[] = [
                        'xp' => -1,
                        'details' => 'Xp deducted for inactivity.',
                        'timestamp' => (int)($cronTime . '000') // Microtime
                    ];
                    $userXp->setAttribute('records', $records);
                    $userXp->save();

                    $profileXp = $profile->getAttribute('xp');
                    $profileXp--;
                    $profile->setAttributes([
                        'xp' => $profileXp,
                        'lastTimeActivityCheck' => (int)$cronTime,
                    ]);

                    $profile->save();
                }
            }

            $daysChecked++;
            unset($logHashMap);
        } while (count($profileHashMap) > 0 && $daysChecked < 5);
    }
}
