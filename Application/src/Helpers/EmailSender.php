<?php

namespace Application\Helpers;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Mailer\MailerInterface;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Mailer\EmailSender as SendEmail;
use Framework\Base\Queue\Adapters\Sync;
use Framework\Base\Queue\TaskQueue;

/**
 * Class EmailSender
 * @package Application\Helpers
 */
class EmailSender
{
    use ApplicationAwareTrait;

    /**
     * @param MailerInterface $mailerInterface
     * @param $mailerClient
     * @param BrunoInterface $model
     * @param $subject
     * @param $html
     * @param null $text
     * @return mixed
     */
    public function sendEmail(
        MailerInterface $mailerInterface,
        $mailerClient,
        BrunoInterface $model,
        $subject,
        $html,
        $text = null
    ) {
        $profileAttributes = $model->getAttributes();
        $appConfiguration = $this->getApplication()
            ->getConfiguration();

        $emailSender = new SendEmail($mailerInterface);
        $emailSender->setClient(
            $mailerClient
        );

        $emailSender->setFrom(
            $appConfiguration
                ->getPathValue('env.PRIVATE_MAIL_FROM')
        );
        $emailSender->setSubject($subject);
        $emailSender->setTo($profileAttributes['email']);
        $emailSender->setHtmlBody($html);
        if ($text !== null) {
            $emailSender->setTextBody($text);
        }

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
