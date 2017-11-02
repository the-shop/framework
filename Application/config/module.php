<?php

use Application\CrudApi\Model\Generic as GenericModel;
use Framework\Base\Mongo\MongoAdapter;
use Application\CrudApi\Repository\GenericRepository;
use Application\Services\SlackService;
use Application\CronJobs\SlackSendMessage;
use Application\CronJobs\AdminsQAWaitingTasks;
use Application\CronJobs\TaskPriorityDeadlineNotification;
use Application\Services\SlackApiClient;
use GuzzleHttp\Client;
use Application\Services\ProfilePerformance;
use Application\Services\EmailService;
use Framework\Base\Mailer\SendGrid;
use SendGrid as SendGridClient;
use Application\Services\FileUploadService;
use Aws\S3\S3Client;
use Framework\Base\FileUpload\S3FileUpload;

return [
    'routePrefix' => '/api/v1',
    'routes' => [
        [
            'post',
            '/upload',
            '\Framework\Http\Controller\FileUploadController::uploadFile'
        ],
        [
            'get',
            '/projects/{id}/uploads',
            '\Framework\Http\Controller\FileUploadController::getProjectUploads'
        ],
        [
            'delete',
            '/upload/{projectId}/project',
            '\Framework\Http\Controller\FileUploadController::deleteProjectUploads'
        ],
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
            MongoAdapter::class,
        ],
        "uploads" => [
            MongoAdapter::class
        ]
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
        'uploads' => MongoAdapter::class,
    ],
    'services' => [
        SlackService::class,
        EmailService::class,
        ProfilePerformance::class,
        FileUploadService::class,
    ],
    'servicesConfig' => [
        SlackService::class => [
            'apiClient' => SlackApiClient::class,
            'httpClient' => Client::class,
        ],
        EmailService::class => [
            'mailerInterface' => SendGrid::class,
            'mailerClient' => [
                'classPath' => SendGridClient::class,
                'constructorArguments' => [
                    getenv('SENDGRID_API_KEY'),
                ],
            ],
        ],
        FileUploadService::class => [
            'fileUploadInterface' => S3FileUpload::class,
            'fileUploadClient' => [
                'classPath' => S3Client::class,
                'constructorArguments' => [
                    'version' => 'latest',
                    'region' => getenv('S3_REGION'),
                    'credentials' => [
                        'key' => getenv('S3_KEY'),
                        'secret' => getenv('S3_SECRET'),
                    ],
                ],
            ],
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
                    '9:00',
                    '14:00',
                ],
            ],
            'args' => [],
        ],
        TaskPriorityDeadlineNotification::class => [
            'timer' => [
                'dailyAt',
                [
                    '7:00',
                ],
            ],
            'args' => [],
        ],
    ],
    'emailTemplatesPath' => '/Application/src/Templates/Emails/profile/'
];
