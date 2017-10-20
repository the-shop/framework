<?php

namespace Application\Test\Application\Traits;

use Framework\Base\Model\BrunoInterface;

trait ProfileRelated
{
    /**
     * @var null|BrunoInterface
     */
    protected $profile = null;

    /**
     * Get profile XP record
     * @return mixed
     */
    public function getXpRecord()
    {
        $repository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('xp');

        $profileXp = $repository->newModel()
            ->setAttribute('records', [])
            ->save();
        $this->profile->setAttribute('xp_id', $profileXp->getAttribute('_id'));

        return $profileXp;
    }

    /**
     * Adds new XP record
     * @param BrunoInterface $xpRecord
     * @param null $timestamp
     * @return BrunoInterface
     */
    public function addXpRecord(BrunoInterface $xpRecord, $timestamp = null)
    {
        if (!$timestamp) {
            $time = new \DateTime();
            $timestamp = $time->format('U');
        }

        $records = $xpRecord->getAttribute('records');
        $records[] = [
            'xp' => 1,
            'details' => 'Testing XP records.',
            'timestamp' => $timestamp
        ];
        $xpRecord->setAttribute('records', $records);
        $xpRecord->save();

        return $xpRecord;
    }
}
