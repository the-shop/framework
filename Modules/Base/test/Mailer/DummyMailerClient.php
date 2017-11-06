<?php

namespace Framework\Base\Test\Mailer;

/**
 * Class DummyMailerClient
 * @package Framework\Base\Test\Mailer
 */
class DummyMailerClient
{
    /**
     * @param $to
     * @param $from
     * @param $subject
     * @param $textBody
     * @param $htmlBody
     * @return string
     */
    public function send($to, $from, $subject, $textBody, $htmlBody)
    {
        if (empty($to) === true || empty($from) === true || empty($subject)) {
            throw new \RuntimeException('Recipient field "to", "from" and "subject" field must be provided.', 403);
        }

        if (empty($htmlBody) === true && empty($textBody)) {
            throw new \RuntimeException('Text-plain or html body is required.', 403);
        }

        return $htmlBody;
    }
}
