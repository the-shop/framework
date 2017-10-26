<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Model\BrunoInterface;

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

            $appConfig = $this->getApplication()
                ->getConfiguration();

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

            $mailSender = $this->getApplication()->getService('emailService');

            $mailSender->sendEmail(
                $appConfig->getPathValue('env.PRIVATE_MAIL_FROM'),
                $appConfig->getPathValue('env.PRIVATE_MAIL_SUBJECT'),
                $payload->getAttribute('email'),
                $htmlBody,
                $textBody
            );
        }

        return $this;
    }
}
