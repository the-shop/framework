<?php

namespace Framework\Base\Application;

/**
 * Interface ConfigurationInterface
 * @package Framework\Base\Application
 */
interface ConfigurationInterface
{
    /**
     * ConfigurationInterface constructor.
     * @param array $configurationValues
     */
    public function __construct(array $configurationValues = []);

    /**
     * @param string $dotPath
     * @param $value
     * @return mixed
     */
    public function setPathValue(string $dotPath, $value);

    /**
     * @param string $dotPath
     * @return mixed
     */
    public function getPathValue(string $dotPath);

    /**
     * @return array
     */
    public function getAll();
}
