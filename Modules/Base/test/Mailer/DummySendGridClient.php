<?php

namespace Framework\Base\Test\Mailer;

use SendGrid\Mail;

/**
 * Class DummySendGridClient
 * @package Framework\Base\Test\Mailer
 */
class DummySendGridClient
{
    /**
     * @var DummySendGridClient
     */
    public $client;

    /**
     * @var null
     */
    private $response = null;

    /**
     * DummySendGridClient constructor.
     */
    public function __construct()
    {
        $this->client = $this;
    }

    /**
     * @return $this
     */
    public function mail()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function send()
    {
        return $this;
    }

    /**
     * @param Mail $mail
     * @return $this
     */
    public function post(Mail $mail)
    {
        $to = $mail->getPersonalizations()[0]->getTos()[0]->getEmail();
        $from = $mail->getFrom()->getEmail();
        $subject = $mail->getSubject();

        $errors = [];

        if (empty($to) === true || empty($from) === true || empty($subject) === true) {
            $error = new \stdClass();
            $error->field = 'recipient.to.from.subject';
            $error->message = 'Recipient field "to", "from" and "subject" field must be provided.';
            $errors[] = $error;
        }

        if (count($errors) > 0) {
            $error = new \stdClass();
            $error->errors = $errors;

            $this->response = $error;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function body()
    {
        return json_encode($this->response);
    }
}
