<?php

namespace Application\Helpers;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Model\BrunoInterface;

class XpRecord
{
    use ApplicationAwareTrait;

    /**
     * @param BrunoInterface $profile
     * @return BrunoInterface|null
     */
    public function getXpRecord(BrunoInterface $profile)
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
}
