<?php

namespace Application\Test\Application\Traits;

use Framework\Base\Model\BrunoInterface;
use MongoDB\BSON\ObjectID;

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

    /**
     * @return BrunoInterface
     */
    public function getNewRequestLog()
    {
        return $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('logs')
            ->newModel()
            ->setAttributes(
                [
                    'name' => '',
                    'id' => '',
                    'ip' => '',
                    'date' => '',
                    'uri' => '',
                    'method' => ''
                ]
            );
    }

    /**
     * @param null $timestamp
     * @return BrunoInterface
     */
    public function getNewRequestLogWithDateUnAssigned($timestamp = null)
    {
        if (!$timestamp) {
            $time = new \DateTime();
            $timestamp = $time->format('U');
        }

        $log = $this->getNewRequestLog();

        $log->setAttributes([
            '_id' => new ObjectID(dechex($timestamp) . '0000000000000000'),
            'date' => (new \DateTime())::createFromFormat('d-m-Y H:i:s', $timestamp)
        ]);

        return $log;
    }

    /**
     * @param null $timestamp
     * @return BrunoInterface
     */
    public function getNewRequestLogWithDateAssigned($timestamp = null)
    {
        if (!$timestamp) {
            $time = new \DateTime();
            $timestamp = $time->format('U');
        }

        $log = $this->getNewRequestLog();

        $log->setAttributes([
            '_id' => new ObjectID(dechex($timestamp) . '0000000000000000'),
            'date' => (new \DateTime())::createFromFormat('d-m-Y H:i:s', $timestamp),
            'id' => $this->profile->getAttribute('_id')
        ]);

        return $log;
    }
}
