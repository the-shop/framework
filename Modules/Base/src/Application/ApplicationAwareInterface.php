<?php

namespace Framework\Base\Application;

/**
 * Interface ApplicationAwareInterface
 * @package Framework\Base\Application
 */
interface ApplicationAwareInterface
{
    /**
     * @param ApplicationInterface $application
     * @return mixed
     */
    public function setApplication(ApplicationInterface $application);

    /**
     * @return mixed
     */
    public function getApplication();
}
