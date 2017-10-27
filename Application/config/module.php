<?php

use Application\CrudApi\Model\Generic as GenericModel;
use Framework\Base\Mongo\MongoAdapter;
use Application\CrudApi\Repository\GenericRepository;
use Application\Services\ProfilePerformance;
use Application\Services\EmailService;
use Framework\Base\Mailer\SendGrid;
use SendGrid as SendGridClient;

return [
    'routePrefix' => '/api/v1',
    'routes' => [
        [
            'get',
            '/{resourceName}/{identifier}/performance',
            '\Application\CrudApi\Controller\Resource::getPerformance',
        ],
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
        GenericModel::class => GenericRepository::class,
    ],
    'modelAdapters' => [
        'users' => [
            MongoAdapter::class,
        ],
        'projects' => [
            MongoAdapter::class,
        ],
        'sprints' => [
            MongoAdapter::class,
        ],
        'settings' => [
            MongoAdapter::class,
        ],
        'tasks' => [
            MongoAdapter::class,
        ],
        'comments' => [
            MongoAdapter::class,
        ],
        "xp" => [
            MongoAdapter::class,
        ],
        "profile_overall" => [
            MongoAdapter::class,
        ],
        "logs" => [
            MongoAdapter::class,
        ]
    ],
    'primaryModelAdapter' => [
        'users' => MongoAdapter::class,
        'projects' => MongoAdapter::class,
        'sprints' => MongoAdapter::class,
        'settings' => MongoAdapter::class,
        'tasks' => MongoAdapter::class,
        'comments' => MongoAdapter::class,
        'xp' => MongoAdapter::class,
        'profile_overall' => MongoAdapter::class,
        'logs' => MongoAdapter::class,
    ],
    'services' => [
        ProfilePerformance::class,
        EmailService::class,
    ],
    'emailService' => [
        'mailerInterface' => SendGrid::class,
        'mailerClient' => [
            'classPath' => SendGridClient::class,
            'constructorArguments' => [
                getenv('SENDGRID_API_KEY'),
            ],
        ],
    ],
];
