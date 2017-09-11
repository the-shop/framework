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
        return 'Email was successfully sent!';
    }
}
