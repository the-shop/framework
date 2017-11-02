<?php

namespace Framework\BaseTest\Mailer;

use Framework\Base\Test\Mailer\DummyMailer;
use Framework\Base\Test\Mailer\DummyMailerClient;
use Framework\Base\Mailer\EmailSender;
use Framework\Base\Test\UnitTest;

/**
 * Class EmailSenderTest
 * @package Framework\BaseTest\Mailer
 */
class EmailSenderTest extends UnitTest
{
    /**
     * Test email sender setter methods - success
     */
    public function testEmailSenderSetterMethods()
    {
        $testData = [
            'htmlBody' => '<h1>Test</h1>',
            'textBody' => 'Test',
            'from' => 'test@test.com',
            'to' => 'test@testing.com',
            'subject' => 'test',
            'options' => [
                'cc' => 'test cc'
            ]
        ];

        $dummyMailer = new DummyMailer();
        $emailSender = new EmailSender($dummyMailer);
        $emailSender->setClient(new DummyMailerClient());
        $emailSender->setHtmlBody($testData['htmlBody']);
        $emailSender->setTextBody($testData['textBody']);
        $emailSender->setFrom($testData['from']);
        $emailSender->setTo($testData['to']);
        $emailSender->setSubject($testData['subject']);
        $emailSender->setOptions($testData['options']);

        $out = $emailSender->send();

        $this->assertEquals($testData['htmlBody'], $dummyMailer->getHtmlBody());
        $this->assertEquals($testData['textBody'], $dummyMailer->getTextBody());
        $this->assertEquals($testData['from'], $dummyMailer->getFrom());
        $this->assertEquals($testData['to'], $dummyMailer->getTo());
        $this->assertEquals($testData['subject'], $dummyMailer->getSubject());
        $this->assertEquals($testData['options'], $dummyMailer->getOptions());
        $this->assertEquals($testData['htmlBody'], $out);
    }

    /**
     * Test email sender set options - invalid input type - exception
     */
    public function testEmailSenderSetOptionsFailInputType()
    {
        $dummyMailer = new DummyMailer();
        $emailSender = new EmailSender($dummyMailer);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Options must be a type of array.');
        $this->expectExceptionCode(403);

        $emailSender->setOptions('test');
    }

    /**
     * Test email sender set options - not allowed option field - exception
     */
    public function testEmailSenderSetOptionsNotAllowedField()
    {
        $dummyMailer = new DummyMailer();
        $emailSender = new EmailSender($dummyMailer);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Option field test is not allowed.");
        $this->expectExceptionCode(403);

        $emailSender->setOptions(['test' => 'test']);
    }
}
