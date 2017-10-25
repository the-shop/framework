<?php

namespace Framework\Base\Application;

/**
 * Interface RegistryInterface
 * @package Framework\Base\Application
 */
interface RegistryInterface extends ApplicationAwareInterface
{
    /**
     * @param $key
     * @param $value
     * @param bool $overwrite
     * @return mixed
     */
    public function register(string $key, $value, bool $overwrite = false);

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @return mixed
     */
    public function delete(string $key);
}
