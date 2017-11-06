<?php

namespace Application\Helpers;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Model\BrunoInterface;
use MongoDB\BSON\ObjectID;

/**
 * Class ProfileOverall
 * @package Application\Helpers
 */
class ProfileOverall
{
    use ApplicationAwareTrait;

    /**
     * Get profile overall record
     * @param BrunoInterface $profile
     * @return BrunoInterface
     */
    public function getProfileOverallRecord(BrunoInterface $profile): BrunoInterface
    {
        $repository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('profile_overall');
        $profileId = $profile->getAttribute('_id');
        $profileOverallRecord = $repository->loadOne($profileId);
        if ($profileOverallRecord === null) {
            $profileOverallRecord =
                $repository->newModel()
                    ->setAttributes([
                        '_id' => new ObjectID($profileId),
                        'totalEarned' => 0,
                        'totalCost' => 0,
                        'profit' => 0,
                    ])
                    ->save();
        }

        return $profileOverallRecord;
    }
}
