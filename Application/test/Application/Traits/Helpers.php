<?php

namespace Application\Test\Application\Traits;

use Application\Test\Application\CronJobs\TaskDeadlineReminderTest;
use Framework\CrudApi\Test\HelperTrait;

/**
 * Class Helpers
 * @package Application\Test\Application\Traits
 */
trait Helpers
{
    use HelperTrait;

    /**
     * Helper for creating message about tasks deadline
     * @param array $tasks
     * @param $format
     * @return bool|string
     */
    private function createMessage($format, array $tasks = [])
    {
        if (empty($tasks)) {
            return false;
        }

        $webDomain = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('env.WEB_DOMAIN');

        $message = '';

        if ($format === TaskDeadlineReminderTest::DUE_DATE_SOON) {
            $message = 'Hey, these tasks *due_date soon*:';
        }
        if ($format === TaskDeadlineReminderTest::DUE_DATE_PASSED) {
            $message = 'Hey, these tasks *due_date has passed*:';
        }

        foreach ($tasks as $task) {
            $taskAttributes = $task->getAttributes();
            $message .= ' *'
                . $taskAttributes['title']
                . ' ('
                . \DateTime::createFromFormat('U', $taskAttributes['due_date'])
                    ->format('Y-m-d')
                . ')* '
                . $webDomain
                . 'projects/'
                . $taskAttributes['project_id']
                . '/sprints/'
                . $taskAttributes['sprint_id']
                . '/tasks/'
                . $taskAttributes['_id']
                . ' ';
        }

        return $message;
    }
}
