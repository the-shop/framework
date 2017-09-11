<?php

namespace Framework\BaseTest\Mailer;

use Framework\Base\Mailer\EmailSender;
use Framework\Base\Mailer\SendGrid;
use Framework\Base\Test\UnitTest;

/**
 * Class SendGridTest
 * @package Framework\BaseTest\Mailer
 */
class SendGridTest extends UnitTest
{
    /**
     * Test SendGrid mailer send email - no html or text body provided - exception
     */
    public function testSendGridSendFailedNoHtmlOrTextBody()
    {
        $sendGrid = new SendGrid();
        $emailSender = new EmailSender($sendGrid);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Text-plain or html body is required.');
        $this->expectExceptionCode(403);

        $emailSender->send();
    }
}
