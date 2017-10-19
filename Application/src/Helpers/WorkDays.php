<?php

namespace Application\Helpers;

/**
 * Class WorkDays
 * @package Application\Helpers
 */
class WorkDays
{
    /**
     * Return list of work days (week days) for current month
     * Class WorkDays
     * @package Application\Helpers
     */
    public static function getWorkDays()
    {
        $firstDayOfMonth = (new \DateTime())->modify('first day of this month');
        $lastDayOfMonth = (new \DateTime())->modify('last day of this month');

        $dates = [];

        for ($date = $firstDayOfMonth; $date <= $lastDayOfMonth; $date->modify('+1 day')) {
            $dateFormatted = $date->format('Y-m-d');
            if (self::isWeekDay($dateFormatted) === true) {
                $dates[] = $dateFormatted;
            }
        }

        return $dates;
    }

    /**
     * @param $date
     * @return bool
     */
    public static function isWeekDay($date)
    {
        return (date('N', strtotime($date)) <= 5) === true;
    }
}
