<?php

namespace Application\Listeners;

use Application\Helpers\EmailSender;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Mailer\SendGrid;
use Framework\Base\Model\BrunoInterface;
use SendGrid as MailerClient;

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
            $message = str_replace(
                '{N}',
                ($xpDifference > 0 ? "+" . $xpDifference : $xpDifference),
                $emailMessage
            );
            $subject = 'Xp status changed!';

            // Try to save model and send confirmation email
                $html = /** @lang text */
                    "<html>
                        <body>
                            <p> $message </p>
                        </body>
                     </html>";

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
