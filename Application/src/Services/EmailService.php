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
     * @param null $textBody
     * @return mixed
     */
    public function sendEmail(
        string $from,
        string $subject,
        string $to,
        $htmlBody,
        $textBody = null
    ) {
        $appConfiguration = $this->getApplication()
            ->getConfiguration();

        $mailerInterfaceClassPath = $appConfiguration
            ->getPathValue('emailService.mailerInterface');
        $mailerClientClassPath = $appConfiguration
            ->getPathValue('emailService.mailerClient.classPath');
        $constructorArguments = array_values(
            $appConfiguration
                ->getPathValue('emailService.mailerClient.constructorArguments')
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
