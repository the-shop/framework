<?php

namespace Framework\Base\Application;

/**
 * Class ServicesRegistry
 * @package Framework\Base\Application
 */
class ServicesRegistry extends BaseRegistry
{
    use ApplicationAwareTrait;
    /**
     * @param ServiceInterface $service
     * @param bool $overwrite
     * @return $this
     */
    public function registerService(ServiceInterface $service, bool $overwrite = false)
    {
        $service->setApplication($this->getApplication());
        $this->register($service->getIdentifier(), $service, $overwrite);

        return $this;
    }

    /**
     * Same functionality as in BaseRegistry but does validation of $value's interface
     *
     * @param string $key
     * @param $value
     * @param bool $overwrite
     * @return BaseRegistry
     */
    public function register(string $key, $value, bool $overwrite = false)
    {
        if ($value instanceof ServiceInterface) {
            return parent::register($key, $value, $overwrite);
        }

        throw new \RuntimeException('Can not register service that does not implement "ServiceInterface"');
    }
}
