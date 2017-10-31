<?php

namespace Application\Listeners;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Model\BrunoInterface;
use Application\Exceptions\UserInputException;

class TaskSettingStatus implements ListenerInterface
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

            //if task is not claimed by user, deny task setting status to be changed
            if (empty($updatedFields) === false) {
                $keysToCheck = ['paused', 'submitted_for_qa', 'passed_qa'];
                $checked = array_intersect_key($updatedFields, array_flip($keysToCheck));
                if ($payload->getAttribute('owner') === null && count($checked) > 0) {
                    throw new UserInputException('Permission denied. Task is not claimed.', 403);
                }
            }
        }
    }
}
