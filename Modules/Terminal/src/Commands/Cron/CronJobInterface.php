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
     * @param array $cronJobParams
     */
    public function __construct(array $cronJobParams);

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
     * @param array $args
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function setArgs(array $args): CronJobInterface;

    /**
     * @return array
     */
    public function getArgs(): array;

    /**
     * @return mixed
     */
    public function execute();
}
