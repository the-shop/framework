<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\MonthlyMinimumCheck;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\UnitTest;

/**
 * Class MonthlyMinimumCheckTest
 * @package Application\Test\Application\CronJobs
 */
class MonthlyMinimumCheckTest extends UnitTest
{
    use ProfileRelated, ProjectRelated, Helpers;

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
                'minimumsMissed' => 0,
                'employee' => true
            ])
            ->save();

        $this->setTaskOwner($newUser);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->profile->delete();
    }

    /**
     * Test monthly minimum missed
     */
    public function testMonthlyMinimumMissed()
    {
        $project = $this->getNewProject();
        $project->save();

        $timeStamp = (int) (new \DateTime())
            ->modify('first day of last month')
            ->format('U');
        $task = $this->getAssignedTask($timeStamp);
        $task->setAttribute('project_id', $project->getAttribute('_id'));
        $task->save();

        $monthlyMinimumCheck = new MonthlyMinimumCheck(['value' => 'daily', 'args' => []]);
        $monthlyMinimumCheck->setApplication($this->getApplication());

        $checkProfileMinimum = $this->profile->getAttribute('minimumsMissed');

        $monthlyMinimumCheck->execute();

        $updatedProfile = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        $this->assertGreaterThan(
            $checkProfileMinimum,
            $updatedProfile->getAttribute('minimumsMissed')
        );
        $this->assertEquals(
            1,
            $updatedProfile->getAttribute('minimumsMissed')
        );
    }
}
