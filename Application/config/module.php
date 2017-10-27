<?php

use Application\CrudApi\Model\Generic as GenericModel;
use Framework\Base\Mongo\MongoAdapter;
use Application\CrudApi\Repository\GenericRepository;
use Application\Services\SlackService;
use Application\CronJobs\SlackSendMessage;
use Application\CronJobs\AdminsQAWaitingTasks;
use Application\Services\SlackApiClient;
use GuzzleHttp\Client;
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
        'slackMessages' => [
            MongoAdapter::class,
        ],
        "xp" => [
            MongoAdapter::class,
        ],
        "profile_overall" => [
            MongoAdapter::class,
        ],
        "logs" => [
            MongoAdapter::class
        ],
    ],
    'primaryModelAdapter' => [
        'users' => MongoAdapter::class,
        'projects' => MongoAdapter::class,
        'sprints' => MongoAdapter::class,
        'settings' => MongoAdapter::class,
        'tasks' => MongoAdapter::class,
        'comments' => MongoAdapter::class,
        'slackMessages' => MongoAdapter::class,
        'xp' => MongoAdapter::class,
        'profile_overall' => MongoAdapter::class,
        'logs' => MongoAdapter::class,
    ],
    'services' => [
        SlackService::class,
        ProfilePerformance::class,
        EmailService::class,
    ],
    'servicesConfig' => [
        SlackService::class => [
            'apiClient' => SlackApiClient::class,
            'httpClient' => Client::class,
        ],
    ],
    'cronJobs' => [
        SlackSendMessage::class => [
            'timer' => 'everyMinute',
            'args' => [],
        ],
        AdminsQAWaitingTasks::class => [
            'timer' => [
                'twiceDaily',
                [
                    '9',
                    '14',
                ],
            ],
            'args' => [],
        ]
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
