<?php

namespace Framework\Base\Mailer;

interface MailerInterface
{
    /**
     * Set recipient
     * @param $to
     * @return mixed
     */
    public function setTo($to);

    /**
     * Set sender
     * @param $from
     * @return mixed
     */
    public function setFrom($from);

    /**
     * Set subject
     * @param $subject
     * @return mixed
     */
    public function setSubject($subject);

    /**
     * Set email text body
     * @param $textBody
     * @return mixed
     */
    public function setTextBody($textBody);

    /**
     * Set email html body
     * @param $htmlBody
     * @return mixed
     */
    public function setHtmlBody($htmlBody);

    /**
     * Set additional headers
     * @param $options
     * @return mixed
     */
    public function setOptions($options);

    /**
     * @param $client
     * @return mixed
     */
    public function setClient($client);

    /**
     * Send email
     * @return mixed
     */
    public function send();
}
