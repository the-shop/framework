<?php

namespace Framework\Base\Application;

class BaseRegistry implements RegistryInterface
{
    private $content = [];

    /**
     * @param $key
     * @param $value
     * @param bool $overwrite
     * @return $this
     */
    public function register($key, $value, $overwrite = false)
    {
        if (array_key_exists($key, $this->content) === true && $overwrite === false) {
            throw new \RuntimeException('Key "' . $key . '" is already registered.');
        }

        $this->content[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->content) === false) {
            throw new \RuntimeException('Key "' . $key . '" is not registered.');
        }

        return $this->content[$key];
    }

    /**
     * @param $key
     * @return $this
     */
    public function delete($key)
    {
        if (array_key_exists($key, $this->content) === true) {
            unset($this->content[$key]);
        }
        return $this;
    }
}
