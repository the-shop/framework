<?php

namespace Framework\Terminal\Commands;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class Test
 * @package Framework\Base\Terminal\Commands
 */
class Test
{
    use ApplicationAwareTrait;

    public function handle($testParam, $optionalParam = null)
    {
        if ($optionalParam === null) {
            $optionalParam = 'equal to null';
        }

        $responseMessage =
            'Command test done and received testParam: <'
            . $testParam
            . '>, optionalParam is: <'
            . $optionalParam
            . '>';

        return $responseMessage;
    }
}
