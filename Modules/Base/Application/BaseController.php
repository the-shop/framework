<?php

namespace Framework\Base\Application;

/**
 * Class BaseController
 * @package Framework\Base\Application
 */
abstract class BaseController implements ControllerInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @return \Framework\Base\Manager\RepositoryInterface
     */
    public function getRepositoryManager()
    {
        if ($this->getApplication()->getRepositoryManager() === null) {
            throw new \RuntimeException('Repository manager not set');
        }

        return $this->getApplication()->getRepositoryManager();
    }

    /**
     * @param $fullyQualifiedClassName
     * @return \Framework\Base\Repository\BrunoRepositoryInterface
     */
    public function getRepository($fullyQualifiedClassName)
    {
        return $this->getRepositoryManager()
            ->getRepository($fullyQualifiedClassName);
    }
}
