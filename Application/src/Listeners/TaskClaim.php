<?php

namespace Application\Listeners;

use Application\Exceptions\UserInputException;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Model\BrunoInterface;

class TaskClaim implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param BrunoInterface $payload
     */
    public function handle($payload)
    {
        // Check if payload is BrunoInterface model and if collection is users
        if (($payload instanceof BrunoInterface) === true && $payload->getCollection() === 'tasks') {
            $updatedFields = $payload->getDirtyAttributes();

            if (empty($updatedFields) === false) {
                if ((isset($updatedFields['owner']) === true
                     || isset($updatedFields['reservationsBy']) === true) === true
                    && ($projectId = $payload->getAttribute('project_id')) !== null
                ) {
                    $taskOwnerId = isset($updatedFields['owner']) === true ?
                        $updatedFields['owner'] : $updatedFields['reservationsBy'][0]['user_id'];

                    // Check if user is a member of project that task belongs to
                    $repository = $this->getApplication()
                                       ->getRepositoryManager()
                                       ->getRepositoryFromResourceName('projects');

                    $project = $repository->loadOne($projectId);

                    if (in_array($taskOwnerId, $project->getAttribute('members')) === false) {
                        throw new UserInputException('Permission denied. Not a member of project.', 403);
                    }

                    // Load configuration
                    $taskReservationTime = $this->getApplication()
                                                ->getConfiguration()
                                                ->getPathValue('internal.tasks.reservation.maxReservationTime');

                    $currentUnixTime = time();

                    $repository = $this->getApplication()
                                       ->getRepositoryManager()
                                       ->getRepositoryFromResourceName('tasks');

                    $query = $repository->createNewQueryForModel($repository->newModel())
                                        ->addAndCondition('_id', '!=', $payload->getId());

                    $allTasks = $repository->loadMultiple($query);

                    foreach ($allTasks as $item) {
                        // Check if user already has some task reserved within reservation time
                        if (empty($reservations = $item->getAttribute('reservationsBy')) === false) {
                            foreach ($reservations as $userReservation) {
                                if ($currentUnixTime - $userReservation['timestamp'] <= ($taskReservationTime * 60)
                                    && $userReservation['user_id'] === $taskOwnerId
                                ) {
                                    throw new UserInputException(
                                        'Permission denied. There is reserved previous task.',
                                        403
                                    );
                                }
                            }
                        }
                        // Check if user has got some unfinished tasks
                        if ($item->getAttribute('owner') === $taskOwnerId
                            && $item->getAttribute('passed_qa') === false
                            && $item->getAttribute('blocked') === false
                            && $item->getAttribute('qa_in_progress') === false
                            && $item->getAttribute('submitted_for_qa') === false
                        ) {
                            throw new UserInputException(
                                'Permission denied. There are unfinished previous tasks.',
                                403
                            );
                        }
                    }
                }
            }
        }
    }
}
