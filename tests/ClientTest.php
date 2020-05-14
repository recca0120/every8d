<?php

namespace Recca0120\Every8d\Tests;

use Carbon\Carbon;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Every8d\Client;

class ClientTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCredit()
    {
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://api.every8d.com/API21/HTTP/getCredit.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query([
                'UID' => $userId,
                'PWD' => $password,
            ])
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = '300'
        );

        $this->assertSame((float) $content, $client->credit());
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionCode 500
     */
    public function testCreditFail()
    {
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://api.every8d.com/API21/HTTP/getCredit.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query([
                'UID' => $userId,
                'PWD' => $password,
            ])
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = '-'
        );

        $client->credit();
    }

    public function testSend()
    {
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'to' => 'foo',
            'text' => 'foo',
            'sendTime' => date('YmdHis'),
        ];

        $query = array_filter(array_merge([
            'UID' => $userId,
            'PWD' => $password,
        ], [
            'SB' => isset($params['subject']) ? $params['subject'] : null,
            'MSG' => $params['text'],
            'DEST' => $params['to'],
            'ST' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null,
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'https://oms.every8d.com/API21/HTTP/sendSMS.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = '285.0,1,1.0,0,d0ad6380-4842-46a5-a1eb-9888e78fefd8'
        );

        $this->assertSame([
            'credit' => 285.0,
            'sended' => 1,
            'cost' => 1.0,
            'unsend' => 0,
            'batchId' => 'd0ad6380-4842-46a5-a1eb-9888e78fefd8',
        ], $client->https()->send($params));
        $this->assertSame((float) '285', $client->credit());
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionCode 500
     */
    public function testSendFail()
    {
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );
        $params = [
            'to' => 'foo',
            'text' => 'foo',
        ];

        $query = array_filter(array_merge([
            'UID' => $userId,
            'PWD' => $password,
        ], [
            'SB' => isset($params['subject']) ? $params['subject'] : null,
            'MSG' => $params['text'],
            'DEST' => $params['to'],
            'ST' => isset($params['ST']) ? Carbon::parse($params['ST'])->format('YmdHis') : null,
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://api.every8d.com/API21/HTTP/sendSMS.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = '-'
        );

        $client->send($params);
    }

    public function testSendMMS()
    {
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'to' => 'foo',
            'text' => 'foo',
            'sendTime' => date('YmdHis'),
            'attachment' => 'attachment',
            'type' => 'jpg',
        ];

        $query = array_filter(array_merge([
            'UID' => $userId,
            'PWD' => $password,
        ], [
            'SB' => isset($params['subject']) ? $params['subject'] : null,
            'MSG' => $params['text'],
            'DEST' => $params['to'],
            'ST' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null,
            'ATTACHMENT' => $params['attachment'],
            'TYPE' => $params['type'],
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'https://oms.every8d.com/API21/HTTP/snedMMS.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = '285.0,1,1.0,0,d0ad6380-4842-46a5-a1eb-9888e78fefd8'
        );

        $this->assertSame([
            'credit' => 285.0,
            'sended' => 1,
            'cost' => 1.0,
            'unsend' => 0,
            'batchId' => 'd0ad6380-4842-46a5-a1eb-9888e78fefd8',
        ], $client->sendMMS($params));
        $this->assertSame((float) '285', $client->credit());
    }
}
