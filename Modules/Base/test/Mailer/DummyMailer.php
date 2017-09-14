<?php

namespace Framework\Base\Test\Mailer;

use Framework\Base\Mailer\Mailer;

/**
 * Class DummyMailer
 * @package Framework\Base\Mailer
 */
class DummyMailer extends Mailer
{
    /**
     * @return string
     */
    public function send()
    {
        $client = $this->getClient();

        return $client->send(
            $this->getTo(),
            $this->getFrom(),
            $this->getSubject(),
            $this->getTextBody(),
            $this->getHtmlBody()
        );
    }
}
