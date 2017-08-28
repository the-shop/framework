<?php

namespace Framework\Base\Mailer;

/**
 * Class Mailer
 * @package App\Services\Email
 */
abstract class Mailer implements MailerInterface
{
    /**
     * @var
     */
    private $to;

    /**
     * @var
     */
    private $from;

    /**
     * @var
     */
    private $subject;

    /**
     * @var string
     */
    private $textBody = '';

    /**
     * @var string
     */
    private $htmlBody = '';

    /**
     * @var array
     */
    private $options = [
        'cc' => '',
        'bcc' => '',
    ];

    /**
     * @param $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @param $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param $textBody
     */
    public function setTextBody($textBody)
    {
        $this->textBody = $textBody;
    }

    /**
     * @param $htmlBody
     */
    public function setHtmlBody($htmlBody)
    {
        $this->htmlBody = $htmlBody;
    }

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getTextBody()
    {
        return $this->textBody;
    }

    /**
     * @return string
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
