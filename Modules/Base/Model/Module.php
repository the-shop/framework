<?php

namespace Framework\Base\Module;

use Framework\Application\RestApi\ApplicationAwareTrait;
use Framework\Base\Database\MongoAdapter;
use Framework\Base\Model\Bruno;

class Module extends BaseModule
{
    use ApplicationAwareTrait;

    private $config = [
        'di' => [
            Bruno::class => [
                MongoAdapter::class
            ]
        ]
    ];

    public function bootstrap()
    {
        $application = $this->getApplication();

        $application->getRepositoryManager()
            ->registerRepositories($this->config['repositories']);
    }
}