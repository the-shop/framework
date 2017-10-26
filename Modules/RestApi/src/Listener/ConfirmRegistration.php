<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Application\Helpers\EmailSender;
use Framework\Base\Mailer\SendGrid;
use Framework\Base\Model\BrunoInterface;
use SendGrid as MailerClient;

/**
 * Class ConfirmRegistration
 * @package Framework\RestApi\Listener
 */
class ConfirmRegistration implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param $payload
     * @return $this
     */
    public function handle($payload)
    {
        if (($payload instanceof BrunoInterface) === true
            && ($payload->getCollection() === 'users') === true
        ) {

            /**
             * @var BrunoInterface $payload
             */
            $profileName = $payload->getAttribute('name');

            $appConfiguration = $this->getApplication()
                ->getConfiguration();

            $subject = $appConfiguration->getPathValue('env.PRIVATE_MAIL_SUBJECT');

            $textBody = 'You have been successfully registered!';
            $htmlBody = /** @lang text */
                "<html>
                    <body>
                        <h3>
                            Hi {$profileName}, you have been successfully registered!
                        </h3>
                    </body>
                </html>
                ";

            $app = $this->getApplication();
            $mailerInterface = new SendGrid();
            $mailerClient = new MailerClient(
                $app->getConfiguration()
                    ->getPathValue('env.SENDGRID_API_KEY')
            );
            $mailer = (new EmailSender())->setApplication($app);

            $mailer->sendEmail(
                $mailerInterface,
                $mailerClient,
                $payload,
                $subject,
                $htmlBody,
                $textBody
            );
        }

        return $this;
    }
}
