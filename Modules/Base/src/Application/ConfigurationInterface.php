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
     * @return string
     */
    public function getIdentifier();

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
    public function getAll(): array;

    /**
     * @param string $path
     *
     * @return \Framework\Base\Application\ConfigurationInterface
     */
    public function readFromJson(string $path): ConfigurationInterface;

    /**
     * @param string $path
     *
     * @return \Framework\Base\Application\ConfigurationInterface
     */
    public function readFromPhp(string $path): ConfigurationInterface;
}
