<?php

namespace Framework\Base\Mailer;

use SendGrid\Content;
use SendGrid\Email as EmailAddress;
use SendGrid\Mail as SendGridEmail;

/**
 * Class SendGrid
 * @package Framework\Base\Src\Mailer
 */
class SendGrid extends Mailer
{
    public function send()
    {
        $emailFrom = $this->getFrom();
        $emailTo = $this->getTo();
        $subject = $this->getSubject();
        $htmlBody = $this->getHtmlBody();
        $textBody = $this->getTextBody();
        $options = $this->getOptions();

        $sg = $this->getClient();

        $from = new EmailAddress($emailFrom, $emailFrom);
        $to = new EmailAddress($emailTo, $emailTo);

        $firstContent = null;
        $secondContent = null;

        if ($textBody === null && $htmlBody === null) {
            throw new \Exception('Text-plain or html body is required.', 403);
        } elseif ($textBody === null && $htmlBody !== null) {
            $firstContent = ['text/html', $htmlBody];
        } elseif ($textBody !== null && $htmlBody === null) {
            $firstContent = ['text/plain', $textBody];
        } else {
            $firstContent = ['text/plain', $textBody];
            $secondContent = ['text/html', $htmlBody];
        }

        $content = new Content($firstContent[0], $firstContent[1]);

        $mail = new SendGridEmail($from, $subject, $to, $content);
        if ($secondContent) {
            $contentHtml = new Content($secondContent[0], $secondContent[1]);
            $mail->addContent($contentHtml);
        }

        if (array_key_exists('cc', $options) && !empty($options['cc'])) {
            $mail->personalization[0]->addCc(['email' => $options['cc']]);
        }
        if (array_key_exists('bcc', $options) && !empty($options['bcc'])) {
            $mail->personalization[0]->addBcc(['email' => $options['bcc']]);
        }

        $response = $sg->client->mail()->send()->post($mail);

        $responseMsg = 'Email was successfully sent!';
        $errors = json_decode($response->body());

        if ($errors) {
            $responseMsg = $errors->errors[0]->message;
        }

        return $responseMsg;
    }
}
