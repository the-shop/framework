<?php

namespace Framework\Base\Mailer;

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
        $clientClassName = $this->getClient();
        $client = new $clientClassName();

        return $client->send(
            $this->getTo(),
            $this->getFrom(),
            $this->getSubject(),
            $this->getTextBody(),
            $this->getHtmlBody()
        );
    }
}
