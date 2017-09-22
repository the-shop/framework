<?php

namespace Framework\Base\Terminal\Commands;

/**
 * Class Test
 * @package Framework\Base\Terminal\Commands
 */
class TestCommand
{
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
