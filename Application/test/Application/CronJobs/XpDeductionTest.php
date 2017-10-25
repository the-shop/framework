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
                'role' => 'standard'
            ])
            ->save();

        $this->setTaskOwner($newUser);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->profile->delete();
    }

    public function testXpDeduction()
    {
        $date = new \DateTime();
        $unixDayAgo = $date->modify('-1 day')->format('U');
        $unix2DaysAgo = $date->modify('-2 day')->format('U');
        $unix3DaysAgo = $date->modify('-3 day')->format('U');
        $unix4DaysAgo = $date->modify('-4 day')->format('U');

        $dates = [
            $unixDayAgo,
            $unix2DaysAgo,
            $unix3DaysAgo,
            $unix4DaysAgo
        ];

        /**
         * @var BrunoInterface[] $logs
         */
        $logs = [];

        for ($i = 0; $i < count($dates); $i++) {
            $logModel = $this->getNewRequestLogWithDateAssigned($dates[$i])
                ->save();
            $logs[] = $logModel;
        }

        $profileAttributes = $this->profile->getAttributes();

        $xpDeduction = new XpDeduction([
            'value' => '',
            'args' => ''
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
}
