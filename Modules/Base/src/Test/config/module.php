<?php

use Framework\Base\Test\TestModel;
use Framework\Base\Test\TestRepository;
use Framework\Base\Test\TestDatabaseAdapter;

return [
    'routePrefix' => '/api/v1',
    'routes' => [
        [
            'get',
            '/{resourceName}',
            '\Application\CrudApi\Controller\Resource::loadAll',
        ],
        [
            'get',
            '/{resourceName}/{identifier}',
            '\Application\CrudApi\Controller\Resource::load',
        ],
        [
            'post',
            '/{resourceName}',
            '\Application\CrudApi\Controller\Resource::create',
        ],
        [
            'put',
            '/{resourceName}/{identifier}',
            '\Application\CrudApi\Controller\Resource::update',
        ],
        [
            'patch',
            '/{resourceName}/{identifier}',
            '\Application\CrudApi\Controller\Resource::partialUpdate',
        ],
        [
            'delete',
            '/{resourceName}/{identifier}',
            '\Application\CrudApi\Controller\Resource::delete',
        ],
    ],
    'repositories' => [
        TestModel::class => TestRepository::class,
    ],
    'modelAdapters' => [
        'tests' => [
            TestDatabaseAdapter::class,
        ],
    ],
    'primaryModelAdapter' => [
        'tests' => TestDatabaseAdapter::class,
    ],
];
