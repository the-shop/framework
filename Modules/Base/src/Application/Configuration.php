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
    public function getAll()
    {
        return $this->configuration;
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
