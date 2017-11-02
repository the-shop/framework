<?php

namespace Application\Services;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\ServiceInterface;
use Framework\Base\Mailer\EmailSender;
use Framework\Base\Queue\Adapters\Sync;
use Framework\Base\Queue\TaskQueue;

/**
 * Class EmailService
 * @package Application\Services
 */
class EmailService implements ServiceInterface
{
    use ApplicationAwareTrait;

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return self::class;
    }

    /**
     * @param string $from
     * @param string $subject
     * @param string $to
     * @param $htmlBody
     * @param array $attachments
     * @param null $textBody
     * @return mixed
     */
    public function sendEmail(
        string $from,
        string $subject,
        string $to,
        $htmlBody,
        $attachments = [],
        $textBody = null
    ) {
        $appConfiguration = $this->getApplication()
            ->getConfiguration();

        $mailerInterfaceClassPath = $appConfiguration
            ->getPathValue('servicesConfig.' . self::class . '.mailerInterface');
        $mailerClientClassPath = $appConfiguration
            ->getPathValue('servicesConfig.' . self::class . '.mailerClient.classPath');
        $constructorArguments = array_values(
            $appConfiguration
                ->getPathValue(
                    'servicesConfig.' . self::class . '.mailerClient.constructorArguments'
                )
        );

        $mailerInterface = new $mailerInterfaceClassPath();

        $mailerClient = new $mailerClientClassPath(...$constructorArguments);

        $emailSender = new EmailSender($mailerInterface);
        $emailSender->setClient(
            $mailerClient
        );
        $emailSender->setFrom($from);
        $emailSender->setSubject($subject);
        $emailSender->setTo($to);
        $emailSender->setHtmlBody($htmlBody);
        if ($textBody !== null) {
            $emailSender->setTextBody($textBody);
        }
        $emailSender->addAttachments($attachments);

        $response = TaskQueue::addTaskToQueue(
            'email',
            Sync::class,
            [
                'taskClassPath' => $emailSender,
                'method' => 'send',
                'parameters' => [],
            ]
        );

        return $response;
    }
}
