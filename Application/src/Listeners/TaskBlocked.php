<?php

namespace Application\Listeners;

use Application\Services\SlackService;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Model\BrunoInterface;

class TaskBlocked implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param BrunoInterface $payload
     *
     * @return mixed
     */
    public function handle($payload)
    {
        // Check if payload is BrunoInterface model and if collection is users
        if (($payload instanceof BrunoInterface) === true|| $payload->getCollection() === 'tasks') {
            $updatedFields = $payload->getDirtyAttributes();

            if (empty($updatedFields) === false) {
                if (isset($updatedFields['blocked']) && $updatedFields['blocked'] === true) {
                    $service = $this->getApplication()
                                    ->getService(SlackService::class);

                    if ($service->getApiClient() === null) {
                        $service->setApiClient();
                    }

                    $repositoryManager = $this->getApplication()
                                              ->getRepositoryManager();

                    $project = $repositoryManager->getRepositoryFromResourceName('projects')
                                                 ->loadOne($payload->getAttribute('project_id'));

                    $owner = $repositoryManager->getRepositoryFromResourceName('users')
                                               ->loadOne($project->getAttribute('acceptedBy'));

                    if ($owner instanceof BrunoInterface
                        && ($recipient = $owner->getAttribute('slack')) !== null) {
                        $webDomain = $this->getApplication()
                                          ->getConfiguration()
                                          ->getPathValue('env.WEB_DOMAIN');

                        $message = 'Hey, task *'
                                   . $payload->getAttribute('title')
                                   . '* is currently blocked! '
                                   . $webDomain
                                   . 'projects/'
                                   . $project->getId()
                                   . '/sprints/'
                                   . $payload->getAttribute('sprint_id')
                                   . '/tasks/'
                                   . $payload->getId();

                        $service->setMessage($recipient, $message, false, SlackService::HIGH_PRIORITY);

                        return true;
                    }
                }
            }
        }
        return false;
    }
}
