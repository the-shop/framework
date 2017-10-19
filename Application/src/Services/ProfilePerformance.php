<?php

namespace Application\Services;

use Application\CrudApi\Model\Generic;
use Framework\Base\Application\ServiceInterface;
use Framework\Base\Helpers\Parse;
use Application\Helpers\ProfileOverall;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Model\BrunoInterface;
use InvalidArgumentException;

/**
 * Class ProfilePerformance
 * @package Application\Services
 */
class ProfilePerformance implements ServiceInterface
{
    use ApplicationAwareTrait;

    /**
     * @var string
     */
    private $identifier = 'profilePerformance';

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param BrunoInterface $profile
     * @param $unixStart
     * @param $unixEnd
     * @return array
     */
    public function aggregateForTimeRange(BrunoInterface $profile, $unixStart, $unixEnd)
    {
        $profile->confirmResourceOf('users');

        // Make sure that unixStart and unixEnd are integer format
        if (!is_int($unixStart) || !is_int($unixEnd)) {
            throw new InvalidArgumentException(
                'Invalid time range input. Must be type of integer',
                403
            );
        }

        $repository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepository(Generic::class)
            ->setResourceName('tasks');

        // Let's grab profile unfinished tasks
        $queryUnfinishedTasks = $repository
            ->createNewQueryForModel($repository->newModel());

        $queryUnfinishedTasks->addAndCondition('owner', '=', $profile->getAttribute('_id'));
        $queryUnfinishedTasks->addAndCondition('passed_qa', '=', false);
        $queryUnfinishedTasks->addAndCondition('timeAssigned', '>=', $unixStart);
        $queryUnfinishedTasks->addAndCondition('timeAssigned', '<=', $unixEnd);

        $profileTasksUnfinished = $repository->loadMultiple($queryUnfinishedTasks);

        // Let's grab profile finished tasks
        $queryFinishedTasks = $repository
            ->createNewQueryForModel($repository->newModel());

        $queryFinishedTasks->addAndCondition('owner', '=', $profile->getAttribute('_id'));
        $queryFinishedTasks->addAndCondition('passed_qa', '=', true);
        $queryFinishedTasks->addAndCondition('timeAssigned', '>=', $unixStart);
        $queryFinishedTasks->addAndCondition('timeAssigned', '<=', $unixEnd);

        $profileTasksFinished = $repository->loadMultiple($queryFinishedTasks);

        $profileTasks = array_merge($profileTasksUnfinished, $profileTasksFinished);

        $estimatedHours = 0;
        $hoursDelivered = 0;
        $totalWorkSeconds = 0;
        $totalNumberFailedQa = 0;
        $totalPayoutInternal = 0;
        $realPayoutInternal = 0;
        $totalPayoutExternal = 0;
        $realPayoutExternal = 0;
        $xpDiff = 0;
        $timeDoingQa = 0;
        $numberOfDays = (int)abs($unixEnd - $unixStart) / (24 * 60 * 60);

        $loadedProjects = [];

        // Let's aggregate task data
        foreach ($profileTasks as $task) {
            // Adjust values for profile we're looking at
            $mappedValues = $this->getTaskValuesForProfile($profile, $task);
            foreach ($mappedValues as $key => $value) {
                $task->setAttribute($key, $value);
            }

            $taskAttributes = $task->getAttributes();

            if (array_key_exists($profile->getAttribute('_id'), $taskAttributes['work'])) {
                foreach ($taskAttributes['work'] as $userId => $workStats) {
                    if ($userId === $profile->getAttribute('_id')) {
                        $estimatedHours += (float)$taskAttributes['estimatedHours'];
                        $timeDoingQa += $workStats['qa_total_time'];
                        $totalWorkSeconds += $workStats['worked'];
                        $totalNumberFailedQa += $workStats['numberFailedQa'];
                    } else {
                        $timeDoingQa += $workStats['qa_total_time'];
                    }
                }
            } else {
                $estimatedHours += (float)$taskAttributes['estimatedHours']; // For old tasks
                // without work field
            }

            // Get the project if not loaded already
            if (array_key_exists($taskAttributes['project_id'], $loadedProjects) === false) {
                $loadedProjects[$taskAttributes['project_id']] =
                    $repository->setResourceName('projects')
                        ->loadOne($taskAttributes['project_id']);
            }

            $project = $loadedProjects[$taskAttributes['project_id']];
            $isInternalProject = $project->getAttribute('isInternal');

            if ($isInternalProject) {
                $totalPayoutInternal += $taskAttributes['payout'];
            } else {
                $totalPayoutExternal += $taskAttributes['payout'];
            }

            if ($taskAttributes['passed_qa']) {
                $hoursDelivered += (int)$taskAttributes['estimatedHours'];

                if ($isInternalProject) {
                    $realPayoutInternal += $taskAttributes['payout'];
                } else {
                    $realPayoutExternal += $taskAttributes['payout'];
                }
            }
        }

        // Let's see the XP diff within time range
        if (empty($profile->getAttribute('xp_id')) !== true) {
            $unixStartDate = Parse::unixTimestamp($unixStart);
            $unixEndDate = Parse::unixTimestamp($unixEnd);
            $xpRecord = $repository->setResourceName('xp')
                ->loadOne($profile->getAttribute('xp_id'));
            if ($xpRecord) {
                foreach ($xpRecord->getAttribute('records') as $record) {
                    $recordTimestamp = Parse::unixTimestamp($record['timestamp']);
                    if ($recordTimestamp >= $unixStartDate && $recordTimestamp <= $unixEndDate) {
                        $xpDiff += $record['xp'];
                    }
                }
            }
        }

        // Sum up totals
        $totalPayoutCombined = $totalPayoutExternal + $totalPayoutInternal;
        $realPayoutCombined = $realPayoutExternal + $realPayoutInternal;
        $timeDoingQaHours = $this->roundFloat(($timeDoingQa / 60 / 60), 2, 5);
        $totalWorkHours = $this->roundFloat($totalWorkSeconds / 60 / 60, 2, 5);
        $qaSuccessRate = $totalNumberFailedQa > 0 ?
            sprintf("%d", $totalNumberFailedQa / count($profileTasks) * 100)
            : sprintf("%d", 100);

        $profileOverall = (new ProfileOverall())->setApplication($this->getApplication())
            ->getProfileOverallRecord($profile);
        $profileOverallAttributes = $profileOverall->getAttributes();

        $out = [
            'estimatedHours' => $estimatedHours,
            'hoursDelivered' => $hoursDelivered,
            'totalWorkHours' => $totalWorkHours,
            'totalPayoutExternal' => $totalPayoutExternal,
            'realPayoutExternal' => $realPayoutExternal,
            'totalPayoutInternal' => $totalPayoutInternal,
            'realPayoutInternal' => $realPayoutInternal,
            'totalPayoutCombined' => $totalPayoutCombined,
            'realPayoutCombined' => $realPayoutCombined,
            'hoursDoingQA' => $timeDoingQaHours,
            'qaSuccessRate' => $qaSuccessRate,
            'xpDiff' => $xpDiff,
            'xpTotal' => $profile->getAttribute('xp'),
            'OverallTotalEarned' => $profileOverallAttributes['totalEarned'],
            'OverallTotalCost' => $profileOverallAttributes['totalCost'],
            'OverallProfit' => $profileOverallAttributes['profit'],
        ];

        $out = array_merge($out, $this->calculateSalary($out, $profile));
        $out = array_merge($out, $this->calculateEarningEstimation($out, $numberOfDays));

        return $out;
    }

    /**
     * Outputs hash map with seconds spent in work, pause and qa together with flag is task completed
     *
     * @param BrunoInterface $task
     * @return array
     */
    public function perTask(BrunoInterface $task)
    {
        $task->confirmResourceOf('tasks');

        $taskAttributes = $task->getAttributes();

        $taskWorkHistory = is_array($taskAttributes['work']) ? $taskAttributes['work'] : [];

        // We'll respond with array of performance per task owner (if task got reassigned for example)
        $response = [];

        // If task was never assigned, there's no performance, respond with empty array
        if (empty($taskWorkHistory) === true) {
            return $response;
        }

        $userPerformance = [
            'taskCompleted' => $task->getAttribute('passed_qa') === true ? true : false,
        ];

        foreach ($taskWorkHistory as $taskOwnerId => $stats) {
            $userPerformance['workSeconds'] = $stats['worked'];
            $userPerformance['pauseSeconds'] = $stats['paused'];
            $userPerformance['qaSeconds'] = $stats['qa'];
            $userPerformance['qaProgressSeconds'] = $stats['qa_in_progress'];
            $userPerformance['qaProgressTotalSeconds'] = $stats['qa_total_time'];
            $userPerformance['totalNumberFailedQa'] = $stats['numberFailedQa'];
            $userPerformance['blockedSeconds'] = $stats['blocked'];
            $userPerformance['workTrackTimestamp'] = $stats['workTrackTimestamp'];

            // Let's just add diff based of last task state against current time if task not done yet
            if (array_key_exists('timeRemoved', $stats) === false
                && $userPerformance['taskCompleted'] !== true
            ) {
                $unixNow = (int)(new \DateTime())->format('U');
                if ($taskAttributes['paused'] !== true
                    && $taskAttributes['blocked'] !== true
                    && $taskAttributes['submitted_for_qa'] !== true
                    && $taskAttributes['qa_in_progress'] !== true
                ) {
                    $userPerformance['workSeconds'] += $unixNow - $stats['workTrackTimestamp'];
                }
                if ($taskAttributes['paused'] === true) {
                    $userPerformance['pauseSeconds'] += $unixNow - $stats['workTrackTimestamp'];
                }
                if ($taskAttributes['submitted_for_qa'] === true) {
                    $userPerformance['qaSeconds'] += $unixNow - $stats['workTrackTimestamp'];
                }
                if ($taskAttributes['blocked'] === true) {
                    $userPerformance['blockedSeconds'] += $unixNow - $stats['workTrackTimestamp'];
                }
                if ($taskAttributes['qa_in_progress'] === true) {
                    $userPerformance['qaProgressSeconds'] += $unixNow - $stats['workTrackTimestamp'];
                    $userPerformance['qaProgressTotalSeconds'] += $unixNow - $stats['workTrackTimestamp'];
                }
            }

            //set last task owner flag so we can calculate payment and XP when task is finished
            if (array_key_exists('timeRemoved', $stats) === false) {
                $userPerformance['taskLastOwner'] = true;
            } else {
                $userPerformance['taskLastOwner'] = false;
            }

            $response[$taskOwnerId] = $userPerformance;

            //remove flag from user performance array because only one user should have it
            unset($userPerformance['taskLastOwner']);
        }

        return $response;
    }

    /**
     * Get task payout, xp award and estimate for specific $profile <-> $task relation
     * @param BrunoInterface $profile
     * @param BrunoInterface $task
     * @return array
     */
    public function getTaskValuesForProfile(BrunoInterface $profile, BrunoInterface $task)
    {
        $xpAwardMultiplyBy = 4;
        $xpDeductionMultiplyBy = 10;

        $xpAward = $this->calculateXpAwardOrDeduction($profile, $task, $xpAwardMultiplyBy);
        $xpDeduction = $this->calculateXpAwardOrDeduction($profile, $task, $xpDeductionMultiplyBy);

        $hourlyRate = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('internal.hourlyRate');

        $out = [];
        $out['xp'] = $xpAward;
        $out['xpDeduction'] = $xpDeduction;
        $out['payout'] = Parse::float($hourlyRate) * $task->getAttribute('estimatedHours');
        $taskNoPayout = $task->getAttribute('noPayout');
        if ($taskNoPayout === true) {
            $out['payout'] = 0;
        }
        $out['estimatedHours'] = $this->calculateTaskEstimatedHours($profile, $task);

        return $out;
    }

    /**
     * Calculate task priority coefficient
     * @param BrunoInterface $taskOwner
     * @param BrunoInterface $task
     * @return float|int
     */
    public function taskPriorityCoefficient(BrunoInterface $taskOwner, BrunoInterface $task)
    {
        $taskPriorityCoefficient = 1;

        // Get all projects that user is a member of
        $repository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepository(Generic::class);

        $projectsQuery = $repository->setResourceName('projects')
            ->createNewQueryForModel($repository->newModel());
        $projectsQuery->whereInArrayCondition('members', [$taskOwner->getAttribute('_id')]);

        $taskOwnerProjects = $repository->loadMultiple($projectsQuery);

        $unassignedTasksPriority = [];

        $repository->setResourceName('tasks');

        // Get all unassigned tasks from projects that user is a member of, and make list of tasks priority
        foreach ($taskOwnerProjects as $project) {
            $projectTasks = $repository->loadMultiple(
                [
                    'project_id' => $project->getAttribute('_id'),
                ]
            );
            foreach ($projectTasks as $projectTask) {
                // Let's compare user skills with task skillset
                $compareSkills = array_intersect(
                    $taskOwner->getAttribute('skills'),
                    $projectTask->getAttribute('skillset')
                );
                if (empty($projectTask->getAttribute('owner') === true)
                    && in_array(
                        $projectTask->getAttribute('priority'),
                        $unassignedTasksPriority
                    ) === false
                    && empty($compareSkills) === false
                ) {
                    $unassignedTasksPriority[$projectTask->getAttribute('_id')] =
                        $projectTask->getAttribute('priority');
                }
            }
        }

        // Check task priority and compare with list of unassigned tasks priority and set task priority coefficient
        if ($task->getAttribute('priority') === 'Low'
            && (
                in_array('Medium', $unassignedTasksPriority) === true
                || in_array('High', $unassignedTasksPriority)
            ) === true
        ) {
            $taskPriorityCoefficient = 0.5;
        }

        if ($task->getAttribute('priority') === 'Medium'
            && in_array('High', $unassignedTasksPriority) === true) {
            $taskPriorityCoefficient = 0.8;
        }

        return $taskPriorityCoefficient;
    }

    /**
     * @param BrunoInterface $taskOwner
     * @param $estimatedHours
     * @param null $multiplyBy
     * @return float|int
     */
    private function getDurationCoefficient(
        BrunoInterface $taskOwner,
        $estimatedHours,
        $multiplyBy = null
    ) {
        $profileCoefficient = 0.9;
        if ($multiplyBy === null) {
            $multiplyBy = 1;
        }
        $taskOwnerXp = $taskOwner->getAttribute('xp');
        if ((float)$taskOwnerXp > 200 && (float)$taskOwnerXp <= 400) {
            $profileCoefficient = 0.8;
        } elseif ((float)$taskOwnerXp > 400 && (float)$taskOwnerXp <= 600) {
            $profileCoefficient = 0.6;
        } elseif ((float)$taskOwnerXp > 600 && (float)$taskOwnerXp <= 800) {
            $profileCoefficient = 0.4;
        } elseif ((float)$taskOwnerXp > 800 && (float)$taskOwnerXp <= 1000) {
            $profileCoefficient = 0.2;
        } elseif ((float)$taskOwnerXp > 1000) {
            $profileCoefficient = 0.1;
        }

        if ((int)$estimatedHours < 10) {
            return ((int)$estimatedHours / 10) * ($profileCoefficient * $multiplyBy);
        }

        return $profileCoefficient * $multiplyBy;
    }

    /**
     * Calculate Xp award or deduction for specific $profile <-> $task relation
     * @param BrunoInterface $profile
     * @param BrunoInterface $task
     * @param null $multiplyBy
     * @return float|int
     */
    private function calculateXpAwardOrDeduction(
        BrunoInterface $profile,
        BrunoInterface $task,
        $multiplyBy =
        null
    ) {
        $xp = (float)$profile->getAttribute('xp');

        $taskComplexity = max((int)$task->getAttribute('complexity'), 1);

        $estimatedHours = $this->calculateTaskEstimatedHours($profile, $task);

        if ($multiplyBy === null) {
            $multiplyBy = 1;
        }

        // Calculate xp award/deduction based on complexity, task priority and duration coefficient
        $taskPriorityCoefficient = null;
        if (isset($task->priorityCoefficient)) {
            $taskPriorityCoefficient = $task->priorityCoefficient;
        } else {
            $taskPriorityCoefficient = $this->taskPriorityCoefficient($profile, $task);
        }

        $calculatedXp = $xp <= 200 ?
            $taskComplexity * $estimatedHours * 10 / $xp * $taskPriorityCoefficient *
            $this->getDurationCoefficient($profile, $estimatedHours, $multiplyBy)
            : $taskPriorityCoefficient *
            $this->getDurationCoefficient($profile, $estimatedHours, $multiplyBy);

        return $calculatedXp;
    }

    /**
     * Calculate task estimated hours for specific $profile <-> $task relation
     * @param BrunoInterface $profile
     * @param BrunoInterface $task
     * @return float
     */
    private function calculateTaskEstimatedHours(BrunoInterface $profile, BrunoInterface $task)
    {
        $estimatedHours =
            (float)$task->getAttribute('estimatedHours') * 1000
            / min((float)$profile->getAttribute('xp'), 1000);

        return $estimatedHours;
    }

    /**
     * Calculates salary based on performance in time range
     * @param array $aggregated
     * @param BrunoInterface $profile
     * @return array
     */
    private function calculateSalary(array $aggregated, BrunoInterface $profile)
    {
        $employeeConfig = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('internal.employees.roles');

        $role = $profile->getAttribute('employeeRole');

        if (isset($employeeConfig[$role]) === false) {
            return [
                'minimalGrossPayout' => 0,
                'realGrossPayout' => 0,
                'grossBonusPayout' => 0,
                'costXpBasedPayout' => 0,
                'employeeRole' => 'Not set',
                'roleMinimumReached' => false,
                'roleMinimum' => 0,
            ];
        }

        $minimum = $employeeConfig[$role]['minimumEarnings'];
        $coefficient = $employeeConfig[$role]['coefficient'];
        $xpEntryPoint = $employeeConfig[$role]['xpEntryPoint'];

        $realPayout = $minimum;

        // Adjust payout based on XP
        $xpInRange = (float)$profile->getAttribute('xp') - $xpEntryPoint;

        if ($xpInRange < 0) {
            $xpInRange = 0;
        } elseif ($xpInRange > 200) {
            $xpInRange = 200;
        }

        // 50% of everything over minimum (from external projects) goes to bonus
        if ($aggregated['realPayoutExternal'] > $minimum) {
            $realPayout = $minimum + ($aggregated['realPayoutExternal'] - $minimum) / 2;
        }

        $costReal = $this->calculateSalaryCostForAmount($realPayout, $coefficient);
        $xpBasedPayout = $costReal * $coefficient * $xpInRange / 2 / 100;
        if ($aggregated['realPayoutCombined'] > $minimum) {
            $costReal += $xpBasedPayout;
        }

        $grossReal = $this->calculateSalaryGrossForAmount($costReal);

        $costGrossMinimum = $this->calculateSalaryCostForAmount($realPayout, $coefficient);
        $grossMinimum = $this->calculateSalaryGrossForAmount($costGrossMinimum);

        $aggregated['costTotal'] = $this->roundFloat($costReal, 2, 10);
        $aggregated['minimalGrossPayout'] = $this->roundFloat($grossMinimum, 2, 10);
        $aggregated['realGrossPayout'] = $this->roundFloat($grossReal, 2, 10);
        $aggregated['grossBonusPayout'] = $this->roundFloat($grossReal - $grossMinimum, 2, 10);
        $aggregated['costXpBasedPayout'] = $xpBasedPayout;
        $aggregated['employeeRole'] = $role;
        $aggregated['roleMinimumReached'] = $grossReal > $grossMinimum;
        $aggregated['roleMinimum'] = $minimum;

        return $aggregated;
    }

    /**
     * Helper to calculate salary based of earned amount
     *
     * @param $forAmount
     * @param $coefficient
     * @return float
     */
    private function calculateSalaryCostForAmount($forAmount, $coefficient)
    {
        $totalCost = $forAmount - $forAmount * $coefficient * 2;

        return $totalCost;
    }

    /**
     * Helper for salary cost conversion to gross payout
     *
     * @param $totalGross
     * @return float
     */
    private function calculateSalaryGrossForAmount($totalGross)
    {
        // 17.2% is fixed cost over gross salary in Croatia
        $gross = $totalGross / 1.172;

        return $gross;
    }

    /**
     * Helper method to round the float correctly
     *
     * @param $float
     * @param $position
     * @param $startAt
     * @return mixed
     */
    private function roundFloat($float, $position, $startAt)
    {
        if ($position < $startAt) {
            $startAt--;
            $newFloat = round($float, $startAt);

            return $this->roundFloat($newFloat, $position, $startAt);
        }

        return $float;
    }

    /**
     * Calculate earning estimation
     * @param array $aggregated
     * @param $numberOfDays
     * @return array
     */
    private function calculateEarningEstimation(array $aggregated, $numberOfDays)
    {
        // Calculate number of days
        $daysInMonth = (int)(new \DateTime())->format('t');
        $daysIn3Months = (int)(new \DateTime())->diff((new \DateTime())->modify('+3 months'))
            ->days;
        $daysIn6Months = (int)(new \DateTime())->diff((new \DateTime())->modify('+6 months'))
            ->days;
        $daysIn12Months = (int)(new \DateTime())->diff((new \DateTime())->modify('+12 months'))
            ->days;

        // Default values if user is not employee so roleMinimum is 0
        if ($aggregated['roleMinimum'] === 0) {
            $aggregated['earnedPercentage'] = sprintf("%d", 0);
            $aggregated['monthPrediction'] = 0;

            return $aggregated;
        }

        $minimumForNumberOfDays = $aggregated['roleMinimum'] / $daysInMonth * $numberOfDays;

        $earnedPercentage = sprintf(
            "%d",
            $aggregated['realPayoutCombined'] / $minimumForNumberOfDays * 100
        );

        // Calculate earning projection
        $monthlyProjection = (float)$aggregated['realPayoutCombined'] / $numberOfDays * $daysInMonth;
        $projectionFor3Months = (float)$aggregated['realPayoutCombined'] / $numberOfDays * $daysIn3Months;
        $projectionFor6Months = (float)$aggregated['realPayoutCombined'] / $numberOfDays * $daysIn6Months;
        $projectionFor12Months = (float)$aggregated['realPayoutCombined'] / $numberOfDays * $daysIn12Months;

        // Total cost of employee per time range
        $totalEmployeeCostPerTimeRange = $aggregated['costTotal'] / $daysInMonth * $numberOfDays;

        // Calculate projection difference employee earned <--> employee cost
        $projectedDifference1Month = $monthlyProjection - $totalEmployeeCostPerTimeRange;
        $projectedDifference3Months = $projectionFor3Months -
            ($aggregated['costTotal'] / $daysInMonth * $daysIn3Months);
        $projectedDifference6Months = $projectionFor6Months -
            ($aggregated['costTotal'] / $daysInMonth * $daysIn6Months);
        $projectedDifference12Months = $projectionFor12Months -
            ($aggregated['costTotal'] / $daysInMonth * $daysIn12Months);

        // Generate output
        $aggregated['earnedPercentage'] = $earnedPercentage;
        $aggregated['monthPrediction'] =
            $this->roundFloat($monthlyProjection, 2, 10);
        $aggregated['totalEmployeeCostPerTimeRange'] =
            $this->roundFloat($totalEmployeeCostPerTimeRange, 2, 10);
        $aggregated['projectedDifference1Month'] =
            $this->roundFloat($projectedDifference1Month, 2, 10);
        $aggregated['projectedDifference3Months'] =
            $this->roundFloat($projectedDifference3Months, 2, 10);
        $aggregated['projectedDifference6Months'] =
            $this->roundFloat($projectedDifference6Months, 2, 10);
        $aggregated['projectedDifference12Months'] =
            $this->roundFloat($projectedDifference12Months, 2, 10);

        return $aggregated;
    }
}
