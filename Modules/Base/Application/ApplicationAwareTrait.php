<?php

namespace Framework\Base\Application;

/**
 * Trait ApplicationAwareTrait
 * @package Framework\Base\Application
 */
trait ApplicationAwareTrait
{
    /**
     * @var ApplicationInterface|null
     */
    private $application;

    /**
     * @param ApplicationInterface $application
     * @return $this
     */
    public function setApplication(ApplicationInterface $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return ApplicationInterface
     */
    public function getApplication()
    {
        return $this->application;
    }
}
