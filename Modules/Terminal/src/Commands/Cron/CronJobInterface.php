<?php

namespace Framework\Terminal\Commands\Cron;

use Framework\Base\Application\ApplicationAwareInterface;

/**
 * Interface CronJobInterface
 * @package Framework\Terminal\Commands\Cron
 */
interface CronJobInterface extends ApplicationAwareInterface
{
    /**
     * CronJobInterface constructor.
     *
     * @param array $cronJob
     */
    public function __construct(array $cronJob);

    /**
     * @param string $expression
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function setCronTimeExpression(string $expression): CronJobInterface;

    /**
     * @return string
     */
    public function getCronTimeExpression(): string;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return mixed
     */
    public function execute();
}
