<?php

namespace Application\Listeners;

use Application\Helpers\ProfileOverall;
use Application\Services\ProfilePerformance;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Model\BrunoInterface;

class TaskStatisticsCalculation implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param BrunoInterface $payload
     */
    public function handle($payload)
    {
        // Check if payload is BrunoInterface model and if collection is users
        if (($payload instanceof BrunoInterface) === true && $payload->getCollection() === 'tasks') {
            $taskOwnerId = $payload->getAttribute('owner');
            $updatedFields = $payload->getDirtyAttributes();

            if ($taskOwnerId !== null && empty($updatedFields) === false) {
                $now = time();

                $work = [
                    $taskOwnerId => [
                        'worked' => 0,
                        'paused' => 0,
                        'qa' => 0,
                        'qa_in_progress' => 0,
                        'qa_total_time' => 0,
                        'numberFailedQa' => 0,
                        'blocked' => 0,
                        'workTrackTimestamp' => $now,
                        'timeAssigned' => $now
                    ]
                ];

                $repository = $this->getApplication()
                                   ->getRepositoryManager()
                                   ->getRepositoryFromResourceName('users');

                $taskOwner = $repository->loadOne($taskOwnerId);

                if ($payload->isNew() === true) {
                    $payload->setAttribute('timeAssigned', $now);
                } elseif (($workOld = $payload->getDatabaseAttribute('work')) !== null) {
                    $work = array_merge($work, $workOld);
                }
                if ($payload->getDatabaseAttribute('priorityCoefficient') === null) {
                    /* On task creation/task claim check if there is owner assigned and set work field and
                    task priority coefficient*/
                    $profilePerformance = (new ProfilePerformance())->setApplication($this->getApplication());
                    $taskPriorityCoefficient = $profilePerformance->taskPriorityCoefficient($taskOwner, $payload);
                    $payload->setAttribute('priorityCoefficient', $taskPriorityCoefficient);
                }
                $previousOwnerId = $payload->getDatabaseAttribute('owner');

                if ($previousOwnerId !== null && $taskOwnerId !== $previousOwnerId) {
                    $calculatedTime = $now - $work[$previousOwnerId]['workTrackTimestamp'];

                    if ($payload->getDatabaseAttribute('paused') === true) {
                        $work[$previousOwnerId]['paused'] += $calculatedTime;
                    } elseif ($payload->getDatabaseAttribute('submitted_for_qa') === true) {
                        $work[$previousOwnerId]['qa'] += $calculatedTime;
                    } elseif ($payload->getDatabaseAttribute('blocked') === true) {
                        $work[$previousOwnerId]['blocked'] += $calculatedTime;
                    } elseif ($payload->getDatabaseAttribute('qa_in_progress') === true) {
                        $work[$previousOwnerId]['qa_in_progress'] += $calculatedTime;
                        $work[$previousOwnerId]['qa_total_time'] += $calculatedTime;
                    } else {
                        $work[$previousOwnerId]['worked'] += $calculatedTime;
                    }
                    $work[$previousOwnerId]['timeRemoved'] = $now;
                    $work[$previousOwnerId]['workTrackTimestamp'] = $now;
                }
                if (isset($work[$taskOwnerId]['timeRemoved']) === true) {
                    unset($work[$taskOwnerId]['timeRemoved']);
                    $work[$taskOwnerId]['workTrackTimestamp'] = $now;
                    $work[$taskOwnerId]['timeAssigned'] = $now;
                }
                $calculatedTime = $now - $work[$taskOwnerId]['workTrackTimestamp'];

                // When task status is paused/resumed calculate time for worked/paused
                if (isset($updatedFields['paused']) === true
                    && isset($updatedFields['qa_in_progress']) === false
                    && isset($updatedFields['submitted_for_qa']) === false
                ) {
                    $updatedFields['paused'] === true ?
                        $work[$taskOwnerId]['worked'] += $calculatedTime :
                        $work[$taskOwnerId]['paused'] += $calculatedTime;
                }
                // When task status is blocked/unblocked calculate time for worked/blocked
                if (isset($updatedFields['blocked']) === true) {
                    $updatedFields['blocked'] === true ?
                        $work[$taskOwnerId]['worked'] += $calculatedTime :
                        $work[$taskOwnerId]['blocked'] += $calculatedTime;
                }
                // When task status is submitted_for_qa calculate time for worked
                if (isset($updatedFields['submitted_for_qa']) === true
                    && isset($updatedFields['qa_in_progress']) === false) {
                    $updatedFields['submitted_for_qa'] === true ?
                        $work[$taskOwnerId]['worked'] += $calculatedTime :
                        $work[$taskOwnerId]['qa'] += $calculatedTime;
                }
                // When task status is set to started or failed QA calculate time for qa_in_progress
                if (isset($updatedFields['qa_in_progress']) === true
                    && isset($updatedFields['passed_qa']) === false) {
                    if ($updatedFields['qa_in_progress'] === true) {
                        $work[$taskOwnerId]['qa'] += $calculatedTime;
                    } else {
                        $work[$taskOwnerId]['qa_in_progress'] = 0;
                        $work[$taskOwnerId]['qa_total_time'] += $calculatedTime;
                        $work[$taskOwnerId]['numberFailedQa'] ++;
                    }
                }
                // When task status is passed_qa update task work timestamp
                if (isset($updatedFields['passed_qa']) === true
                    && $updatedFields['passed_qa'] === true
                    && isset($updatedFields['qa_in_progress']) === true
                    && $updatedFields['qa_in_progress'] === false
                ) {
                    $work[$taskOwnerId]['qa_in_progress'] += $calculatedTime;
                    $work[$taskOwnerId]['qa_total_time'] += $calculatedTime;

                    // Update profile overall stats
                    $profileOverall = (new ProfileOverall)->setApplication($this->getApplication())
                                                          ->getProfileOverallRecord($taskOwner);
                    $profileOverall->setAttribute(
                        'totalEarned',
                        $profileOverall->getAttribute('totalEarned')
                        + $payload->getAttribute('payout')
                    );
                    $profileOverall->setAttribute(
                        'profit',
                        $profileOverall->getAttribute('totalEarned')
                        - $profileOverall->getAttribute('totalCost')
                    );
                    $profileOverall->save();
                }
                $work[$taskOwnerId]['workTrackTimestamp'] = $now;
                $payload->setAttribute('work', $work);

                return $payload;
            }
        }
        return false;
    }
}
