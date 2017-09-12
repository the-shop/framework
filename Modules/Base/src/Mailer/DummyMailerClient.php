<?php

namespace Framework\Base\Mailer;

/**
 * Class DummyMailerClient
 * @package Framework\Base\Mailer
 */
class DummyMailerClient
{
    /**
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $textBody
     * @param string $htmlBody
     * @return string
     */
    public function send($to = '', $from = '', $subject = '', $textBody = '', $htmlBody = '')
    {
        if (empty($to) === true || empty($from) === true || empty($subject)) {
            throw new \RuntimeException('Recipient field "to" must be provided.', 403);
        }

        if (empty($textBody) && empty($htmlBody)) {
            throw new \RuntimeException('Text-plain or html body is required.', 403);
        }

        return 'Email was successfully sent!';
    }
}
