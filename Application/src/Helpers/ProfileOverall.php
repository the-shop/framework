<?php

namespace Application\Helpers;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Model\BrunoInterface;

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
     * @return BrunoInterface|null
     */
    public function getProfileOverallRecord(BrunoInterface $profile)
    {
        $repository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('profile_overall');
        $profileOverallRecord = $repository->loadOne($profile->getAttribute('_id'));
        if (!$profileOverallRecord) {
            $profileOverallRecord =
                $repository->newModel()
                    ->setAttributes([
                        '_id' => $profile->getAttribute('_id'),
                        'totalEarned' => 0,
                        'totalCost' => 0,
                        'profit' => 0,
                    ])
                    ->save();
        }

        return $profileOverallRecord;
    }
}
