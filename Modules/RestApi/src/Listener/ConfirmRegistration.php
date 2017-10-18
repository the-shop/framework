<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Mailer\EmailSender;
use Framework\Base\Mailer\SendGrid;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Queue\Adapters\Sync;
use Framework\Base\Queue\TaskQueue;

class ConfirmRegistration implements ListenerInterface
{
    use ApplicationAwareTrait;

    public function handle($payload)
    {
        if (($payload instanceof BrunoInterface) === true
            && ($payload->getCollection() === 'users') === true
        ) {
            $profileAttributes = $payload->getAttributes();

            $appConfiguration = $this->getApplication()
                ->getConfiguration();
            $emailSender = new EmailSender(new SendGrid());
            $emailSender->setClient(
                new \SendGrid($appConfiguration->getPathValue('env.SENDGRID_API_KEY'))
            );
            $emailSender->setFrom(
                $appConfiguration
                    ->getPathValue('env.PRIVATE_MAIL_FROM')
            );
            $emailSender->setSubject(
                $appConfiguration
                    ->getPathValue('env.PRIVATE_MAIL_SUBJECT')
            );
            $emailSender->setTo($profileAttributes['email']);
            $emailSender->setTextBody('You have been successfully registered!');
            $emailSender->setHtmlBody(
                /** @lang text */
                "<html>
                    <body>
                        <h3>
                            You have been successfully registered!
                        </h3>
                    </body>
                </html>
                "
            );

            return TaskQueue::addTaskToQueue(
                'email',
                Sync::class,
                [
                    'taskClassPath' => $emailSender,
                    'method' => 'send',
                    'parameters' => [],
                ]
            );
        }
    }
}
