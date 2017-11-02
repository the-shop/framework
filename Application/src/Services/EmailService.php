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
     * @param $htmlBodyOptions
     * @param null $textBody
     * @return mixed
     */
    public function sendEmail(
        string $from,
        string $subject,
        string $to,
        $htmlBodyOptions,
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
        $emailSender->setHtmlBody($this->generateHtml($htmlBodyOptions));
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

    /**
     * @param array $htmlOptions
     * @return bool|mixed|string
     */
    private function generateHtml(array $htmlOptions = [])
    {
        if (array_key_exists('template', $htmlOptions) === false
            || array_key_exists('data', $htmlOptions) === false
            || array_key_exists('dataTemplate', $htmlOptions['data']) === false
            || array_key_exists('dataToFill', $htmlOptions['data']) === false
        ) {
            throw new \InvalidArgumentException('html options array not formatted correctly.', 400);
        }

        $htmlTemplate = $htmlOptions['template'];

        if (is_file($htmlOptions['template']) === true) {
            $htmlTemplate = file_get_contents($htmlOptions['template']);
        }

        if (empty($htmlOptions['data']['dataTemplate']) === false) {
            $dataTemplate = null;
            if (is_file($htmlOptions['data']['dataTemplate']) === true) {
                $dataTemplate = file_get_contents($htmlOptions['data']['dataTemplate']);
            }
            if ($dataTemplate !== null) {
                foreach ($htmlOptions['data']['dataToFill'] as $data) {
                    $dataFilledTemplate = $dataTemplate;

                    foreach ($data as $k => $v) {
                        $search = "{{" . "$" . $k . "}}";
                        $dataFilledTemplate = str_replace($search, $v, $dataFilledTemplate);
                    }

                    $htmlTemplate .= $dataFilledTemplate;
                }
            }
        }

        return $htmlTemplate;
    }
}
