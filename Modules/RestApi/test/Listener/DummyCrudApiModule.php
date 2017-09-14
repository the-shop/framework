<?php

namespace Framework\RestApi\Test\Listener;

use Framework\Base\Module\BaseModule;
use Application\CrudApi\Model\Generic as GenericModel;
use Application\CrudApi\Repository\GenericRepository;

/**
 * Class Api
 * @package Application\CrudApi
 */
class DummyCrudApiModule extends BaseModule
{
    private $config = [
        'routes' => [
            [
                'get',
                '/{test}',
                '\Application\CrudApi\Controller\Resource::loadAll'
            ]
        ],
        'repositories' => [
            GenericModel::class => GenericRepository::class
        ]
    ];

    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        $application = $this->getApplication();

        $application->getDispatcher()
            ->addRoutes($this->config['routes']);
    }
}
