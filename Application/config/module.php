<?php

use Application\CrudApi\Model\Generic as GenericModel;
use Framework\Base\Mongo\MongoAdapter;
use Application\CrudApi\Repository\GenericRepository;

return [
    'routes' => [
        'withoutPrefix' => [],
        'withPrefix' => [
            '/api/v1' => [
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
        ],
    ],
    'repositories' => [
        GenericModel::class => GenericRepository::class,
    ],
    'modelAdapters' => [
        'users' => [
            MongoAdapter::class,
        ],
    ],
    'primaryModelAdapter' => [
        'users' => MongoAdapter::class,
    ],
];
