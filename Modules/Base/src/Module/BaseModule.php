<?php

namespace Framework\Base\Module;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class BaseModule
 * @package Framework\Base\Module
 */
abstract class BaseModule implements ModuleInterface
{
    use ApplicationAwareTrait;

    /**
     * @param string $path Full path to file
     *
     * @return mixed
     */
    public function readDecodedJsonFile(string $path)
    {
        if (is_file($path) === false) {
            return null;
        }

        return json_decode(file_get_contents($path), true);
    }
}
