<?php

namespace Recca0120\Every8d\Tests;

use Carbon\Carbon;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Every8d\Every8dMessage;

class Every8dMessageTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testConstruct()
    {
        $message = new Every8dMessage(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }

    public function testSubject()
    {
        $message = new Every8dMessage();
        $message->subject(
            $subject = 'foo'
        );

        $this->assertSame($subject, $message->subject);
    }

    public function testContent()
    {
        $message = new Every8dMessage();
        $message->content(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }

    public function testSendTime()
    {
        $message = new Every8dMessage();
        $message->sendTime(
            $sendTime = Carbon::now()
        );

        $this->assertSame($sendTime, $message->sendTime);
    }

    public function testCreate()
    {
        $message = Every8dMessage::create(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }
}
