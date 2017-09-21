<?php

namespace Framework\Base\Terminal\Commands;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class Test
 * @package Framework\Base\Terminal\Commands
 */
class Test implements CommandInterface
{
    use ApplicationAwareTrait;

    /**
     * @return string
     */
    public function handle()
    {
        return $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->newModel()
            ->setAttribute('name', 'testing')
            ->save();
    }
}
