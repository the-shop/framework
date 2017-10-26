<?php

namespace Framework\Terminal\Commands\Cron;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class CronJobsHandler
 * @package Framework\Base\Terminal\Commands\Cron
 */
abstract class CronJob implements CronJobInterface
{
    use ApplicationAwareTrait;

    /**
     * @var string
     */
    private $cronTimeExpression = '0 0 * * 0';

    /**
     * CronJob constructor.
     *
     * @param array $cronJob
     */
    public function __construct(array $cronJob)
    {
        if (method_exists($this, $cronJob['value']) === false) {
            $this->setCronTimeExpression($cronJob['value']);
        } elseif (empty($cronJob['args']) === true) {
            $this->{$cronJob['value']}();
        } else {
            $this->{$cronJob['value']}(...$cronJob['args']);
        }
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return get_class($this);
    }

    /**
     * Set the cronTimeExpression expression for the event.
     * @param string $expression
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function setCronTimeExpression(string $expression): CronJobInterface
    {
        $this->cronTimeExpression = $expression;

        return $this;
    }

    /**
     * Get the cronTimeExpression expression for the event.
     *
     * @return string
     */
    public function getCronTimeExpression(): string
    {
        return $this->cronTimeExpression;
    }

    /**
     * Schedule the event to run hourly.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function hourly()
    {
        return $this->setCronTimeExpression('0 * * * *');
    }

    /**
     * Schedule the event to run daily.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function daily()
    {
        return $this->setCronTimeExpression('0 0 * * *');
    }

    /**
     * Schedule the command at a given time.
     *
     * @param  string  $time
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function at($time)
    {
        return $this->dailyAt($time);
    }

    /**
     * Schedule the event to run daily at a given time (10:00, 19:30, etc).
     *
     * @param  string  $time
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function dailyAt($time)
    {
        $segments = explode(':', $time);

        return $this->spliceIntoPosition(2, (int) $segments[0])
                    ->spliceIntoPosition(1, count($segments) == 2 ? (int) $segments[1] : '0');
    }

    /**
     * Schedule the event to run twice daily.
     *
     * @param  int  $first
     * @param  int  $second
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function twiceDaily($first = 1, $second = 13)
    {
        $hours = $first.','.$second;

        return $this->spliceIntoPosition(1, 0)
                    ->spliceIntoPosition(2, $hours);
    }

    /**
     * Schedule the event to run only on weekdays.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function weekdays()
    {
        return $this->spliceIntoPosition(5, '1-5');
    }

    /**
     * Schedule the event to run only on Mondays.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function mondays()
    {
        return $this->days(1);
    }

    /**
     * Schedule the event to run only on Tuesdays.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function tuesdays()
    {
        return $this->days(2);
    }

    /**
     * Schedule the event to run only on Wednesdays.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function wednesdays()
    {
        return $this->days(3);
    }

    /**
     * Schedule the event to run only on Thursdays.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function thursdays()
    {
        return $this->days(4);
    }

    /**
     * Schedule the event to run only on Fridays.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function fridays()
    {
        return $this->days(5);
    }

    /**
     * Schedule the event to run only on Saturdays.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function saturdays()
    {
        return $this->days(6);
    }

    /**
     * Schedule the event to run only on Sundays.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function sundays()
    {
        return $this->days(0);
    }

    /**
     * Schedule the event to run weekly.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function weekly()
    {
        return $this->setCronTimeExpression('0 0 * * 0');
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     *
     * @param  int  $day
     * @param  string  $time
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function weeklyOn($day, $time = '0:0')
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(5, $day);
    }

    /**
     * Schedule the event to run monthly.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function monthly()
    {
        return $this->setCronTimeExpression('0 0 1 * *');
    }

    /**
     * Schedule the event to run monthly on a given day and time.
     *
     * @param int  $day
     * @param string  $time
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function monthlyOn($day = 1, $time = '0:0')
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $day);
    }

    /**
     * Schedule the event to run quarterly.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function quarterly()
    {
        return $this->setCronTimeExpression('0 0 1 */3');
    }

    /**
     * Schedule the event to run yearly.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function yearly()
    {
        return $this->setCronTimeExpression('0 0 1 1 *');
    }

    /**
     * Schedule the event to run every minute.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function everyMinute()
    {
        return $this->setCronTimeExpression('* * * * *');
    }

    /**
     * Schedule the event to run every five minutes.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function everyFiveMinutes()
    {
        return $this->setCronTimeExpression('*/5 * * * *');
    }

    /**
     * Schedule the event to run every ten minutes.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function everyTenMinutes()
    {
        return $this->setCronTimeExpression('*/10 * * * *');
    }

    /**
     * Schedule the event to run every thirty minutes.
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function everyThirtyMinutes()
    {
        return $this->setCronTimeExpression('0,30 * * * *');
    }

    /**
     * Set the days of the week the command should run on.
     *
     * @param  array|mixed  $days
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    public function days($days)
    {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(',', $days));
    }

    /**
     * Splice the given value into the given position of the expression.
     *
     * @param  int  $position
     * @param  string  $value
     *
     * @return \Framework\Terminal\Commands\Cron\CronJobInterface
     */
    protected function spliceIntoPosition($position, $value)
    {
        $segments = explode(' ', $this->cronTimeExpression);

        $segments[$position - 1] = $value;

        return $this->setCronTimeExpression(implode(' ', $segments));
    }
}
