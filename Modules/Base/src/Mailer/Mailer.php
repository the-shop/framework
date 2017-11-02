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
     * @var null
     */
    private $textBody = null;

    /**
     * @var null
     */
    private $htmlBody = null;

    /**
     * @var array
     */
    private $options = [
        'cc' => '',
        'bcc' => '',
    ];

    /**
     * @var array
     */
    private $attachments = [];

    /**
     * @var
     */
    private $client;

    /**
     * @param $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param $textBody
     * @return $this
     */
    public function setTextBody($textBody)
    {
        $this->textBody = $textBody;

        return $this;
    }

    /**
     * @param $htmlBody
     * @return $this
     */
    public function setHtmlBody($htmlBody)
    {
        $this->htmlBody = $htmlBody;

        return $this;
    }

    /**
     * @param $options
     * @return $this
     * @throws \Exception
     */
    public function setOptions($options)
    {
        if (!is_array($options)) {
            throw new \Exception("Options must be a type of array.", 403);
        }

        foreach ($options as $key => $value) {
            if (!array_key_exists($key, $this->getOptions())) {
                throw new \Exception("Option field " . $key . " is not allowed.", 403);
            }
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @param $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
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

    /**
     * @param array $attachments
     * @return MailerInterface
     */
    public function addAttachments(array $attachments = [])
    {
        foreach ($attachments as $fileNameAndExtension => $content) {
            $this->addAttachment($fileNameAndExtension, $content);
        }

        return $this;
    }

    /**
     * @param string $fileName
     * @param $content
     * @return MailerInterface
     */
    public function addAttachment(string $fileName, $content)
    {
        $this->attachments[$fileName] = $content;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
}
