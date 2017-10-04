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

    /**
     * @param string $configDirPath Full path to module config directory
     * @return $this
     */
    public function setModuleConfiguration(string $configDirPath)
    {
        $configFiles = $this->getDirContents($configDirPath);

        $appConfiguration = $this->getApplication()->getConfiguration();

        foreach ($configFiles as $configFilePath) {
            if (strpos($configFilePath, '.json') !== false) {
                $appConfiguration->readFromJson($configFilePath);
            }
            if (strpos($configFilePath, '.php') !== false) {
                $appConfiguration->readFromPhp($configFilePath);
            }
        }

        return $this;
    }

    /**
     * Get directory contents, return array of file paths
     * @param string $path Full path to directory
     * @return array
     */
    private function getDirContents(string $path)
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir() === false) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
