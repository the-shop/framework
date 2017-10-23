<?php

namespace Application\Listeners;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Helpers\Parse;
use Framework\Base\Model\BrunoInterface;

/**
 * Class TaskUpdateXP
 * @package App\Listeners
 */
class UpdateXp implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param $payload
     * @return bool
     */
    public function handle($payload)
    {
        // Check if payload is BrunoInterface model and if collection is tasks
        if (($payload instanceof BrunoInterface) === false || $payload->getCollection() !== 'tasks') {
            return false;
        }

        /**
         * @var BrunoInterface $task
         */
        $task = $payload;

        // Make sure not to update user XP if task is edited after QA has passed
        if ($task->isDirty() !== true) {
            return false;
        } else {
            $updatedFields = $task->getDirtyAttributes();
            if (array_key_exists('passed_qa', $updatedFields) === false
                || $updatedFields['passed_qa'] !== true
            ) {
                return false;
            }
        }

        $app = $this->getApplication();
        $repositoryManager = $app->getRepositoryManager();

        $profilePerformance = $app->getService('profilePerformance');

        $taskPerformance = $profilePerformance->perTask($task);

        foreach ($taskPerformance as $profileId => $taskDetails) {
            if ($taskDetails['taskLastOwner'] === false) {
                continue;
            }

            $taskOwnerProfile =
                $repositoryManager
                    ->getRepositoryFromResourceName('users')
                    ->loadOne($profileId);

            $mappedValues = $profilePerformance->getTaskValuesForProfile($taskOwnerProfile, $task);

            $webDomain = $app->getConfiguration()->getPathValue('env.WEB_DOMAIN');
            $taskLink = '['
                . $task->getAttribute('title')
                . ']('
                . $webDomain
                . 'projects/'
                . $task->getAttribute('project_id')
                . '/sprints/'
                . $task->getAttribute('sprint_id')
                . '/tasks/'
                . $task->getAttribute('_id')
                . ')';

            $taskFinishedUnixTime = Parse::unixTimestamp($taskDetails['workTrackTimestamp']);
            $taskDueDateUnixTime = Parse::unixTimestamp($task->getAttribute('due_date'));
            $taskFinishedDate = \DateTime::createFromFormat('U', $taskFinishedUnixTime)->format('Y-m-d');
            $taskDueDate = \DateTime::createFromFormat('U', $taskDueDateUnixTime)->format('Y-m-d');
            $taskXp = (float)$mappedValues['xp'];
            $taskXpDeduction = (float)$mappedValues['xpDeduction'];

            if ($taskFinishedDate <= $taskDueDate) {
                $xpDiff = $taskXp;
                $message = 'Task Delivered on time: ' . $taskLink;
            } else {
                $xpDiff = $taskXpDeduction < 1 ? -1 : -($taskXpDeduction); // Deduct at least 1 xp
                $message = 'Late task delivery: ' . $taskLink;
            }

            if ($xpDiff !== 0) {
                $profileXpRecord = $this->getXpRecord($taskOwnerProfile);

                $records = $profileXpRecord->getAttribute('records');
                $records[] = [
                    'xp' => $xpDiff,
                    'details' => $message,
                    'timestamp' => (int)((new \DateTime())->format('U') . '000') // Microtime
                ];
                $profileXpRecord->setAttribute('records', $records);
                $profileXpRecord->save();

                $updatedXp = $taskOwnerProfile->getAttribute('xp');
                $updatedXp += $xpDiff;

                $taskOwnerProfile->setAttribute('xp', $updatedXp);
                $taskOwnerProfile->save();

                $this->sendSlackMessageXpUpdated($taskOwnerProfile, $task, $xpDiff);
            }

            if ($taskDetails['qaProgressSeconds'] > 30 * 60) {
                $poXpDiff = -3;
                $poMessage = 'Failed to review PR in time for ' . $taskLink;
            } else {
                $poXpDiff = 0.25;
                $poMessage = 'Review PR in time for ' . $taskLink;
            }

            // Get project owner id
            $project = $repositoryManager
                ->getRepositoryFromResourceName('projects')
                ->loadOne($task->getAttribute('project_id'));
            $projectOwner = null;
            if ($project) {
                $projectOwner =
                    $repositoryManager
                        ->getRepositoryFromResourceName('users')
                        ->loadOne($project->getAttribute('acceptedBy'));
            }

            if ($projectOwner) {
                $projectOwnerXpRecord = $this->getXpRecord($projectOwner);
                $records = $projectOwnerXpRecord->getAttribute('records');
                $records[] = [
                    'xp' => $poXpDiff,
                    'details' => $poMessage,
                    'timestamp' => (int)((new \DateTime())->format('U') . '000') // Microtime
                ];
                $projectOwnerXpRecord->setAttribute('records', $records);
                $projectOwnerXpRecord->save();

                $updatedPoXp = $taskOwnerProfile->getAttribute('xp');
                $updatedPoXp += $poXpDiff;

                $projectOwner->setAttribute('xp', $updatedPoXp);
                $projectOwner->save();

                $this->sendSlackMessageXpUpdated($projectOwner, $task, $poXpDiff);
            }
        }

        return true;
    }

    /**
     * @param BrunoInterface $profile
     * @return BrunoInterface|null
     */
    private function getXpRecord(BrunoInterface $profile)
    {
        $profile->confirmResourceOf('users');

        $xpRepository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('xp');

        if (!$profile->getAttribute('xp_id')) {
            /**
             * @var BrunoInterface $profileXp
             */
            $profileXp = $xpRepository->newModel()
                ->setAttribute('records', [])
                ->save();

            $profile->setAttribute('xp_id', $profileXp->getAttribute('_id'));
        } else {
            $profileXp = $xpRepository->loadOne($profile->getAttribute('xp_id'));
        }

        return $profileXp;
    }

    /**
     * Send slack message about XP change
     * @param $profile
     * @param $task
     * @param $xpDiff
     */
    private function sendSlackMessageXpUpdated($profile, $task, $xpDiff)
    {
        $configuration = $this->getApplication()->getConfiguration();
        $xpUpdateMessage = $configuration->getPathValue('.internal.profile_update_xp_message');
        $webDomain = $configuration->getPathValue('env.WEB_DOMAIN');
        $recipient = '@' . $profile->getAttribute('slack');
        $slackMessage = str_replace('{N}', ($xpDiff > 0 ? "+" . $xpDiff : $xpDiff), $xpUpdateMessage)
            . ' *'
            . $task->getAttribute('title')
            . '* ('
            . $webDomain
            . 'projects/'
            . $task->getAttribute('project_id')
            . '/sprints/'
            . $task->getAttribute('sprint_id')
            . '/tasks/'
            . $task->getAttribute('_id')
            . ')';
        //TODO: implement after slack service is implemented
        //Slack::sendMessage($recipient, $slackMessage, Slack::HIGH_PRIORITY);
    }
}