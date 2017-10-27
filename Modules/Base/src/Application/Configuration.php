<?php

namespace Framework\Base\Application;

/**
 * Class Configuration
 * @package Framework\Base\Application
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var array
     */
    private $configuration = [];

    /**
     * Configuration constructor.
     * @param array $configurationValues
     */
    public function __construct(array $configurationValues = [])
    {
        $this->configuration = $configurationValues;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return self::class;
    }

    /**
     * @param string $dotPath
     * @param $value
     * @return $this
     */
    public function setPathValue(string $dotPath, $value)
    {
        $arrayPath = explode('.', $dotPath);

        $this->arrayPath($arrayPath, $this->configuration, $value);

        return $this;
    }

    /**
     * @param string $dotPath
     * @return mixed
     */
    public function getPathValue(string $dotPath)
    {
        $arrayPath = explode('.', $dotPath);

        return $this->arrayPath($arrayPath, $this->configuration);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->configuration;
    }

    /**
     * @param string $path
     * @return $this
     * @todo discuss renaming to loadFromPhp
     */
    public function readFromPhp(string $path): ConfigurationInterface
    {
        if (is_file($path) === false) {
            throw new \RuntimeException(
                'Unable to open php file' . $path . ' file not found!',
                404
            );
        }

        $config = include $path;

        $this->configuration = array_merge($this->configuration, $config);

        return $this;
    }

    /**
     * @param string $path
     * @return $this
     * @todo discuss renaming to loadFromJson
     */
    public function readFromJson(string $path): ConfigurationInterface
    {
        if (is_file($path) === false) {
            throw new \RuntimeException(
                'Unable to open json file' . $path . ' file not found!',
                404
            );
        }

        $config = json_decode(file_get_contents($path), true);

        $this->configuration = array_merge($this->configuration, $config);

        return $this;
    }

    /**
     * @param $array
     * @param array $pathParts
     * @param null $value
     * @return mixed
     */
    private function arrayPath(array $pathParts, &$array, &$value = null)
    {
        $args = func_get_args();
        $ref = &$array;

        foreach ($pathParts as $key) {
            if (is_array($ref) === false) {
                $ref = [];
            }
            $ref = &$ref[$key];
        }

        $prev = $ref;

        if (array_key_exists(2, $args) === true) {
            // value param was passed -> we're setting
            $ref = $value;  // set the value
        }

        return $prev;
    }
}
