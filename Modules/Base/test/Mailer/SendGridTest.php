<?php

namespace Framework\Base\Test\Mailer;

use Framework\Base\Application\Exception\ValidationException;
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
     * Test SendGrid send email without recipient "to" field - exception
     */
    public function testSendGridSendFailedNoRecipientTo()
    {
        $sendGrid = new SendGrid();
        $emailSender = new EmailSender($sendGrid);
        $emailSender->setClient(new DummySendGridClient());
        $emailSender->setFrom('test@test.com');
        $emailSender->setHtmlBody('<h1>test</h1>');
        $emailSender->setTextBody('test');
        $emailSender->setSubject('test');

        $this->expectException(ValidationException::class);

        $emailSender->send();
    }

    /**
     * Test SendGrid send email without "from" field - exception
     */
    public function testSendGridSendFailedNoRecipientFrom()
    {
        $sendGrid = new SendGrid();
        $emailSender = new EmailSender($sendGrid);
        $emailSender->setClient(new DummySendGridClient());
        $emailSender->setTo('test@test.com');
        $emailSender->setHtmlBody('<h1>test</h1>');
        $emailSender->setTextBody('test');
        $emailSender->setSubject('test');

        $this->expectException(ValidationException::class);

        $out = $emailSender->send();
    }

    /**
     * Test SendGrid send email without "subject" field - exception
     */
    public function testSendGridSendFailedNoRecipientSubject()
    {
        $sendGrid = new SendGrid();
        $emailSender = new EmailSender($sendGrid);
        $emailSender->setClient(new DummySendGridClient());
        $emailSender->setTo('test@test.com');
        $emailSender->setFrom('test@test.com');
        $emailSender->setHtmlBody('<h1>test</h1>');
        $emailSender->setTextBody('test');

        $this->expectException(ValidationException::class);

        $emailSender->send();
    }

    /**
     * Test SendGrid send email - no html or text body provided - exception
     */
    public function testSendGridSendFailedNoHtmlOrTextBody()
    {
        $sendGrid = new SendGrid();
        $emailSender = new EmailSender($sendGrid);
        $emailSender->setClient(new DummySendGridClient());
        $emailSender->setTo('test@test.com');
        $emailSender->setFrom('test@test.com');
        $emailSender->setSubject('test@test.com');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Text-plain or html body is required.');
        $this->expectExceptionCode(403);

        $emailSender->send();
    }

    /**
     * Test SendGrid send email - successful
     */
    public function testSendGridSendMailSuccessfully()
    {
        $sendGrid = new SendGrid();
        $emailSender = new EmailSender($sendGrid);
        $emailSender->setClient(new DummySendGridClient());
        $emailSender->setTo('test@test.com');
        $emailSender->setFrom('test@test.com');
        $emailSender->setSubject('test@test.com');
        $emailSender->setHtmlBody('<h1>test</h1>');
        $emailSender->setTextBody('test');

        $this->assertEquals(
            'Email was successfully sent!',
            $emailSender->send()
        );
    }
}
