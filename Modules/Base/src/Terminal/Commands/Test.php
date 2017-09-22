<?php

namespace Framework\Base\Terminal\Commands;

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
        return new \stdClass();
    }
}
