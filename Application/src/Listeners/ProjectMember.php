<?php

namespace Application\Listeners;

use Application\Services\SlackService;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Model\BrunoInterface;

class ProjectMember implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param BrunoInterface $payload
     */
    public function handle($payload)
    {
        // Check if payload is BrunoInterface model and if collection is tasks
        if (($payload instanceof BrunoInterface) === true && $payload->getCollection() === 'projects') {
            $updatedFields = $payload->getDirtyAttributes();

            if (empty($updatedFields) === false) {
                $oldFields = $payload->getDatabaseAttributes();
                if (isset($updatedFields['members']) === true
                    && empty($updatedFields['members']) === false
                ) {
                    $repository = $this->getApplication()
                                       ->getRepositoryManager()
                                       ->getRepositoryFromResourceName('users');

                    $service = $this->getApplication()
                                    ->getService(SlackService::class);

                    if ($service->getApiClient() === null) {
                        $service->setApiClient();
                    }

                    $webDomain = $this->getApplication()
                                      ->getConfiguration()
                                      ->getPathValue('env.WEB_DOMAIN');

                    $projectName = $payload->getAttribute('name');
                    $projectId = $payload->getId();

                    //if user is added to project send slack notification
                    foreach ($updatedFields['members'] as $newMemberId) {
                        if (in_array($newMemberId, $oldFields['members']) === false) {
                            $member = $repository->loadOne($newMemberId);
                            if (($recipient = $member->getAttribute('slack')) !== null) {
                                $message = 'Hey, you\'ve just been added to project '
                                           . $projectName
                                           . ' ('
                                           . $webDomain
                                           . 'projects/'
                                           . $projectId
                                           . ')';

                                $service->setMessage($recipient, $message, false, SlackService::HIGH_PRIORITY);
                            }
                        }
                    }
                    //if user is removed from project send slack notification
                    foreach ($oldFields['members'] as $oldMemberId) {
                        if (in_array($oldMemberId, $updatedFields['members']) === false) {
                            $member = $repository->loadOne($oldMemberId);
                            if ((($recipient = $member->getAttribute('slack')) !== null)) {
                                $message = 'Hey, you\'ve just been removed from project '
                                           . $projectName
                                           . ' ('
                                           . $webDomain
                                           . 'projects/'
                                           . $projectId
                                           . ')';

                                $service->setMessage($recipient, $message, false, SlackService::HIGH_PRIORITY);
                            }
                        }
                    }
                }
            }
        }
    }
}
