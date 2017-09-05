<?php

namespace Framework\Base\Mailer;

/**
 * Class EmailSender
 * @package App\Services
 */
class EmailSender
{
    /**
     * @var MailerInterface
     */
    private $mailerInterface;

    /**
     * EmailSender constructor.
     * @param MailerInterface $mailerInterface
     */
    public function __construct(MailerInterface $mailerInterface)
    {
        $this->mailerInterface = $mailerInterface;
    }

    /**
     * @param $to
     */
    public function setTo($to)
    {
        $this->mailerInterface->setTo($to);
    }

    /**
     * @param $from
     */
    public function setFrom($from)
    {
        $this->mailerInterface->setFrom($from);
    }

    /**
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->mailerInterface->setSubject($subject);
    }

    /**
     * @param $textBody
     */
    public function setTextBody($textBody)
    {
        $this->mailerInterface->setTextBody($textBody);
    }

    /**
     * @param $htmlBody
     */
    public function setHtmlBody($htmlBody)
    {
        $this->mailerInterface->setHtmlBody($htmlBody);
    }

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $this->mailerInterface->setOptions($options);
    }

    public function send()
    {
        $this->mailerInterface->send();
    }
}
