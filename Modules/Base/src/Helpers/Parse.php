<?php

namespace Framework\Base\Helpers;

/**
 * Class Parse
 * @package Framework\Base\Helpers
 */
class Parse
{
    /**
     * Returns integer of timestamp if sent in seconds or microseconds, throws exception otherwise
     *
     * @param $input
     * @return int
     * @throws \Exception
     */
    public static function unixTimestamp($input)
    {
        if (strlen((string)$input) === 10) {
            return (int)$input;
        } elseif (strlen((string)$input) === 13) {
            return (int)substr((string)$input, 0, -3);
        }

        throw new \Exception('Unrecognized unix timestamp format.');
    }

    /**
     * @param $float
     * @return float
     * @throws \Exception
     */
    public static function float($float)
    {
        if (is_float($float)) {
            return $float;
        }

        if ((float)$float == $float) {
            return (float)$float;
        }

        throw new \Exception('Input not a float.');
    }

    /**
     * @param $input
     * @return int
     * @throws \Exception
     */
    public static function integer($input)
    {
        if (is_numeric($input) === true) {
            return (int)$input;
        }

        throw new \Exception('Input not an integer.');
    }
}
