<?php

namespace Framework\Base\Application;

/**
 * Class ServicesRegistry
 * @package Framework\Base\Application
 */
class ServicesRegistry extends BaseRegistry
{
    /**
     * @param $key
     * @param ServiceInterface $service
     * @param bool $overwrite
     * @return $this
     */
    public function registerService($key, ServiceInterface $service, $overwrite = false)
    {
        $this->register($key, $service, $overwrite);

        return $this;
    }

    /**
     * Same functionality as in BaseRegistry but does validation of $value interface
     *
     * @param $key
     * @param $value
     * @param bool $overwrite
     * @return BaseRegistry
     */
    public function register($key, $value, $overwrite = false)
    {
        if ($value instanceof ServiceInterface) {
            return parent::register($key, $value, $overwrite);
        }

        throw new \RuntimeException('Can not register service that does not implement "ServiceInterface"');
    }
}
