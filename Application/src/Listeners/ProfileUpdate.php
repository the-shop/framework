<?php

namespace Application\Listeners;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Model\BrunoInterface;
use Application\Services\EmailService;

/**
 * Class ProfileUpdate
 * @package Application\Listeners
 */
class ProfileUpdate implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param $payload
     * @return $this|bool
     */
    public function handle($payload)
    {
        // Check if payload is BrunoInterface model and if collection is users
        if (($payload instanceof BrunoInterface) === false || $payload->getCollection() !== 'users') {
            return false;
        }

        /**
         * @var BrunoInterface $payload
         */
        $profileChanges = $payload->getDirtyAttributes();

        if (key_exists('xp', $profileChanges)) {
            // Send email with XP status changed
            $oldXp = $payload->getDatabaseAttribute('xp');
            $xpDifference = $profileChanges['xp'] - $oldXp;

            $emailMessage = $this->getApplication()
                ->getConfiguration()
                ->getPathValue('internal.profile_update_xp_message');
            //Format message
            $message = str_replace(
                '{N}',
                ($xpDifference > 0 ? "+" . $xpDifference : $xpDifference),
                $emailMessage
            );

            $subject = 'Xp status changed!';
            $html = /** @lang text */
                "<html>
                    <body>
                        <p> {$message} </p>
                    </body>
                 </html>";


            $appConfig = $this->getApplication()->getConfiguration();
            $mailSender = $this->getApplication()->getService('emailService');
            /**
             * @var EmailService $mailSender
             */
            $mailSender->sendEmail(
                $appConfig->getPathValue('env.PRIVATE_MAIL_FROM'),
                $subject,
                $payload->getAttribute('email'),
                $html
            );

            $slack = $payload->getAttribute('slack');

            if ($slack !== null && empty($slack) === false) {
                //Send slack message with XP status changed
                $recipient = '@' . $slack;
                //TODO: implement after slack service is implemented
                //Slack::sendMessage($recipient, $message, Slack::HIGH_PRIORITY);
            }
        }

        return $this;
    }
}
