<?php

namespace Framework\Base\Application;

interface RegistryInterface
{
    /**
     * @param $key
     * @param $value
     * @param bool $overwrite
     * @return mixed
     */
    public function register($key, $value, $overwrite = false);

    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @return mixed
     */
    public function delete($key);
}
