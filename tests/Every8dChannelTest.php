<?php

namespace Recca0120\Every8d\Tests;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Every8d\Every8dChannel;
use Recca0120\Every8d\Every8dMessage;

class Every8dChannelTest extends TestCase
{
    protected function setUp()
    {
        if (version_compare(PHP_VERSION, '5.6', '<') === true) {
            $this->markTestSkipped('PHP VERSION must bigger then 5.6');
        }
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testSend()
    {
        $channel = new Every8dChannel(
            $client = m::mock('Recca0120\Every8d\Client')
        );

        $client->shouldReceive('send')->with([
            'subject' => null,
            'to' => $to = '+1234567890',
            'text' => $message = 'foo',
            'sendTime' => null,
        ])->once();

        $channel->send(
            new TestNotifiable(function () use ($to) {
                return $to;
            }),
            new TestNotification(function () use ($message) {
                return $message;
            })
        );
    }

    public function testSendMessage()
    {
        $channel = new Every8dChannel(
            $client = m::mock('Recca0120\Every8d\Client')
        );

        $client->shouldReceive('send')->with([
            'subject' => $subject = 'bar',
            'to' => $to = '+1234567890',
            'text' => $message = 'foo',
            'sendTime' => $sendTime = date('YmdHis'),
        ])->once();

        $channel->send(
            new TestNotifiable(function () use ($to) {
                return $to;
            }),
            new TestNotification(function () use ($subject, $message, $sendTime) {
                return Every8dMessage::create($message)->subject($subject)->sendTime($sendTime);
            })
        );
    }

    public function testSendFail()
    {
        $channel = new Every8dChannel(
            $client = m::mock('Recca0120\Every8d\Client')
        );

        $channel->send(
            new TestNotifiable(function () {
                return false;
            }),
            new TestNotification(function () {
                return false;
            })
        );
    }
}

if (class_exists(Notification::class) === true) {
    class TestNotifiable
    {
        use Notifiable;

        protected $resolver;

        public function __construct($resolver)
        {
            $this->resolver = $resolver;
        }

        public function routeNotificationForEvery8d()
        {
            $resolver = $this->resolver;

            return $resolver();
        }
    }

    class TestNotification extends Notification
    {
        protected $resolver;

        public function __construct($resolver)
        {
            $this->resolver = $resolver;
        }

        public function toEvery8d()
        {
            $resolver = $this->resolver;

            return $resolver();
        }
    }
}
