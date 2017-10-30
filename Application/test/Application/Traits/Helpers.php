<?php

namespace Application\Test\Application\Traits;

use Application\Test\Application\CronJobs\TaskDeadlineReminderTest;

/**
 * Class Helpers
 * @package Application\Test\Application\Traits
 */
trait Helpers
{
    /**
     * Helper method for generating random E-mail
     * @param int $length
     * @return string
     */
    public function generateRandomEmail(int $length = 10)
    {
        $email = $this->generateRandomString($length);

        $email .= '@test.com';

        return $email;
    }

    public function generateRandomString(int $length = 10)
    {
        // Generate random email
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, $charactersLength - 1)];
        }

        return $string;
    }

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

    /**
     * Deletes test records from db collection
     * @param string $resourceName
     */
    private function purgeCollection(string $resourceName)
    {
        $adapter = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName($resourceName)
            ->getPrimaryAdapter();

        $databaseName = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('env.DATABASE_NAME');

        $adapter->getClient()
            ->selectCollection($databaseName, $resourceName)
            ->drop();
    }
}
