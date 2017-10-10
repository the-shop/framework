<?php

namespace Framework\RestApi\Listener;

use Application\CrudApi\Model\Generic;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Mailer\EmailSender;
use Framework\Base\Mailer\SendGrid;
use Framework\Base\Queue\Adapters\Sync;
use Framework\Base\Queue\TaskQueue;

class ForgotPassword implements ListenerInterface
{
    use ApplicationAwareTrait;

    public function handle($payload)
    {
        if (($payload instanceof Generic) === true
            && ($payload->getCollection() === 'users') === true
        ) {
            $userAttributes = $payload->getAttributes();

            if (array_key_exists('passwordForgot', $userAttributes) === true
                && true === ($userAttributes['passwordForgot'] === true)
            ) {
                // Generate random token and timestamp and set to profile
                $passwordResetToken = md5(uniqid(rand(), true));
                $passwordResetTime = (new \DateTime())->format('U');
                $payload->setAttributes(
                    [
                        'passwordResetToken' => $passwordResetToken,
                        'passwordResetTime' => $passwordResetTime,
                    ]
                );
                $payload->save();

                $this->sendPasswordResetEmail($payload);
            }
        }
    }

    private function sendPasswordResetEmail($payload)
    {
        $profileAttributes = $payload->getAttributes();

        // Send email with link for password reset
        $webDomain = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('env.WEB_DOMAIN');
        $webDomain .= 'reset-password';

        $subject = 'Password reset confirmation link!';

        $html = "<html>
                    <body>
                    <p> Please, visit this link below to change your password.</p>
                    <p> 
                    <a href=\"{$webDomain}?token={$profileAttributes['passwordResetToken']}\">
                    Click here to set a new password.
                    </a></p>
                    </body>
                </html>";

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
        $emailSender->setSubject($subject);
        $emailSender->setTo($profileAttributes['email']);
        $emailSender->setTextBody('Password reset requested.');
        $emailSender->setHtmlBody($html);

        TaskQueue::addTaskToQueue(
            'email',
            Sync::class,
            [
                'taskClassPath' => $emailSender,
                'method' => 'send',
                'parameters' => [],
            ]
        );

        return 'You will shortly receive an email with the link to reset your password.';
    }
}
