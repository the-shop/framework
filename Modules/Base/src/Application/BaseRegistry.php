<?php

namespace Framework\Base\Application;

/**
 * Class BaseRegistry
 * @package Framework\Base\Application
 */
class BaseRegistry implements RegistryInterface
{
    use ApplicationAwareTrait;

    /**
     * @var array
     */
    private $content = [];

    /**
     * @param $key
     * @param $value
     * @param bool $overwrite
     * @return $this
     */
    public function register(string $key, $value, bool $overwrite = false)
    {
        if (array_key_exists($key, $this->content) === true && $overwrite === false) {
            throw new \RuntimeException('Key "' . $key . '" is already registered.');
        }

        $this->content[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->content) === false) {
            throw new \RuntimeException('Key "' . $key . '" is not registered.');
        }

        return $this->content[$key];
    }

    /**
     * @param string $key
     * @return $this
     */
    public function delete(string $key)
    {
        if (array_key_exists($key, $this->content) === true) {
            unset($this->content[$key]);
        }

        return $this;
    }
}
