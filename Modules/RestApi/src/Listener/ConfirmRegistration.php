<?php

namespace Framework\RestApi\Listener;

use Application\CrudApi\Model\Generic;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Mailer\EmailSender;
use Framework\Base\Mailer\SendGrid;

class ConfirmRegistration implements ListenerInterface
{
    use ApplicationAwareTrait;

    public function handle($payload)
    {
        if ($payload instanceof Generic && $payload->getCollection() === 'users') {
            $profileAttributes = $payload->getAttributes();

            $emailSender = new EmailSender(new SendGrid());
            $emailSender->setClient(new \SendGrid(getenv('SENDGRID_API_KEY')));
            $emailSender->setFrom(
                getenv('PRIVATE_MAIL_FROMM')
            );
            $emailSender->setSubject(
                getenv('PRIVATE_MAIL_SUBJECTT')
            );
            $emailSender->setTo($profileAttributes['email']);
            $emailSender->setTextBody('You have been successfully registered!');
            $emailSender->setHtmlBody(
                "<html>
                    <body>
                        <h3>
                            You have been successfully registered!
                        </h3>
                    </body>
                </html>
                "
            );
            $emailSender->send();
        }

        return $this;
    }
}
