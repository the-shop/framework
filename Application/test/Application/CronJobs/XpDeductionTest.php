<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\XpDeduction;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Test\UnitTest;

class XpDeductionTest extends UnitTest
{
    use ProjectRelated, ProfileRelated, Helpers;

    /**
     * @var array
     */
    private $logDates = [];

    public function setUp()
    {
        parent::setUp();
        $newUser = $this->getApplication()->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->newModel()
            ->setAttributes([
                'name' => 'test user',
                'email' => $this->generateRandomEmail(20),
                'password' => 'test',
                'skills' => ['PHP'],
                'xp' => 200,
                'employeeRole' => 'Apprentice',
                'role' => 'standard',
            ])
            ->save();

        $this->setTaskOwner($newUser);

        $date = new \DateTime();
        $unixDayAgo = $date->modify('-1 day')->format('U');
        $unix2DaysAgo = $date->modify('-2 day')->format('U');
        $unix3DaysAgo = $date->modify('-3 day')->format('U');
        $unix4DaysAgo = $date->modify('-4 day')->format('U');

        $this->logDates = [
            $unixDayAgo,
            $unix2DaysAgo,
            $unix3DaysAgo,
            $unix4DaysAgo,
        ];
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->profile->delete();
    }

    /**
     * Xp deducted - profile was inactive for 3 days
     */
    public function testXpDeduction()
    {
        /**
         * @var BrunoInterface[] $logs
         */
        $logs = [];

        for ($i = 0; $i < count($this->logDates); $i++) {
            $logModel = $this->getNewRequestLogWithDateUnAssigned($this->logDates[$i])
                ->save();
            $logs[] = $logModel;
        }

        $profileAttributes = $this->profile->getAttributes();

        $xpDeduction = new XpDeduction([
            'value' => '',
            'args' => '',
        ]);
        $xpDeduction->setApplication($this->getApplication());
        $xpDeduction->execute();

        $updatedProfileAttributes = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($profileAttributes['_id'])
            ->getAttributes();

        foreach ($logs as $log) {
            $log->delete();
        }

        $xpRecord = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('xp')
            ->loadOne($updatedProfileAttributes['xp_id']);

        $xpDeductedRecord = $xpRecord->getAttributes()['records'][0];

        $this->assertGreaterThan($updatedProfileAttributes['xp'], $profileAttributes['xp']);
        $this->assertEquals(199, $updatedProfileAttributes['xp']);
        $this->assertArrayHasKey('xp', $xpDeductedRecord);
        $this->assertArrayHasKey('details', $xpDeductedRecord);
        $this->assertArrayHasKey('timestamp', $xpDeductedRecord);
        $this->assertEquals(-1, $xpDeductedRecord['xp']);
        $this->assertEquals('Xp deducted for inactivity.', $xpDeductedRecord['details']);
    }

    /**
     * Xp deducted and profile banned because total XP is now 0
     */
    public function testXpDeductedAndUserBanned()
    {
        /**
         * @var BrunoInterface[] $logs
         */
        $logs = [];

        for ($i = 0; $i < count($this->logDates); $i++) {
            $logModel = $this->getNewRequestLogWithDateUnAssigned($this->logDates[$i])
                ->save();
            $logs[] = $logModel;
        }

        $this->profile->setAttribute('xp', 1);
        $this->profile->save();

        $profileAttributes = $this->profile->getAttributes();

        $xpDeduction = new XpDeduction([
            'value' => '',
            'args' => '',
        ]);

        $xpDeduction->setApplication($this->getApplication());
        $xpDeduction->execute();

        $updatedProfileAttributes = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($profileAttributes['_id'])
            ->getAttributes();

        foreach ($logs as $log) {
            $log->delete();
        }

        $xpRecord = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('xp')
            ->loadOne($updatedProfileAttributes['xp_id']);

        $xpDeductedRecord = $xpRecord->getAttributes()['records'][0];

        $this->assertLessThan($profileAttributes['xp'], $updatedProfileAttributes['xp']);
        $this->assertEquals(0, $updatedProfileAttributes['xp']);
        $this->assertArrayHasKey('xp', $xpDeductedRecord);
        $this->assertArrayHasKey('details', $xpDeductedRecord);
        $this->assertArrayHasKey('timestamp', $xpDeductedRecord);
        $this->assertEquals(-1, $xpDeductedRecord['xp']);
        $this->assertEquals('Xp deducted for inactivity.', $xpDeductedRecord['details']);
        $this->assertArrayHasKey('banned', $updatedProfileAttributes);
        $this->assertEquals(true, $updatedProfileAttributes['banned']);
    }

    /**
     * Don't deduct profile XP if profile is inactive
     */
    public function testXpNotDeductedProfileInactive()
    {
        /**
         * @var BrunoInterface[] $logs
         */
        $logs = [];

        for ($i = 0; $i < count($this->logDates); $i++) {
            $logModel = $this->getNewRequestLogWithDateUnAssigned($this->logDates[$i])
                ->save();
            $logs[] = $logModel;
        }

        $this->profile->setAttribute('active', false);
        $this->profile->save();

        $profileAttributes = $this->profile->getAttributes();

        $xpDeduction = new XpDeduction([
            'value' => '',
            'args' => '',
        ]);

        $xpDeduction->setApplication($this->getApplication());
        $xpDeduction->execute();

        $updatedProfileAttributes = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($profileAttributes['_id'])
            ->getAttributes();

        foreach ($logs as $log) {
            $log->delete();
        }

        $this->assertEquals($profileAttributes['xp'], $updatedProfileAttributes['xp']);
        $this->assertEquals(200, $updatedProfileAttributes['xp']);
        $this->assertArrayNotHasKey('xp_id', $updatedProfileAttributes);
    }

    /**
     * Don't deduct profile XP due to activity in last 3 days
     */
    public function testXpNotDeductedProfileWasActiveWithinLastThreeDays()
    {
        /**
         * @var BrunoInterface[] $logs
         */
        $logs = [];

        for ($i = 0; $i < count($this->logDates); $i++) {
            $logModel = $this->getNewRequestLogWithDateUnAssigned($this->logDates[$i]);
            if ($i === 2 || $i === 3) {
                $logModel = $this->getNewRequestLogWithDateAssigned($this->logDates[$i]);
            }
            $logModel->save();
            $logs[] = $logModel;
        }

        $this->profile->setAttribute('active', false);
        $this->profile->save();

        $profileAttributes = $this->profile->getAttributes();

        $xpDeduction = new XpDeduction([
            'value' => '',
            'args' => '',
        ]);

        $xpDeduction->setApplication($this->getApplication());
        $xpDeduction->execute();

        $updatedProfileAttributes = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($profileAttributes['_id'])
            ->getAttributes();

        foreach ($logs as $log) {
            $log->delete();
        }

        $this->assertEquals($profileAttributes['xp'], $updatedProfileAttributes['xp']);
        $this->assertEquals(200, $updatedProfileAttributes['xp']);
        $this->assertArrayNotHasKey('xp_id', $updatedProfileAttributes);
    }
}
