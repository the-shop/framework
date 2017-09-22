<?php

namespace Framework\Base\Terminal\Commands\Cron;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Terminal\Commands\CommandInterface;

/**
 * Class CronJob
 * @package Framework\Base\Terminal\Commands\Cron
 */
class CronJob extends Schedule implements CommandInterface
{
    use ApplicationAwareTrait;

    private $registeredJobs = [];

    public function handle()
    {
        $this->addCronJob(
            'test',
            $this->dailyAt('13:00')->getCronTimeExpression(),
            [
                'testParam' => 'test required param',
                'testOptionalParam' => 'test optional param'
            ]
        );
    }

    public function getRegisteredJobs()
    {
        return $this->registeredJobs;
    }

    private function addCronJob(
        string $commandName,
        string $timeExpression,
        array $parameters = []
    ) {
        $routes = $this->getApplication()->getDispatcher()->getRoutes();

        // Let's check if command is registered
        if (array_key_exists($commandName, $routes) === false) {
            throw new \InvalidArgumentException(
                'Command name ' . $commandName . ' is not registered.',
                404
            );
        }

        $this->registeredJobs[] = [
            $commandName,
            $timeExpression,
            $parameters,
        ];

        return $this;
    }
}
