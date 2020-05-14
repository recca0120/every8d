<?php

namespace Recca0120\Every8d;

use Carbon\Carbon;
use DomainException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;

class Client
{
    /**
     * $apiEndpoint.
     *
     * @var string
     */
    public $apiEndpoint = 'http://api.every8d.com/API21/HTTP';

    /**
     * $credit.
     *
     * @var float
     */
    public $credit = null;

    /**
     * $userId.
     *
     * @var string
     */
    protected $userId;

    /**
     * $password.
     *
     * @var string
     */
    protected $password;

    /**
     * $httpClient.
     *
     * @var \Http\Client\HttpClient
     */
    protected $httpClient;

    /**
     * $messageFactory.
     *
     * @var \Http\Message\MessageFactory
     */
    protected $messageFactory;

    /**
     * __construct.
     *
     * @param string $userId
     * @param string $password
     * @param \Http\Client\HttpClient $httpClient
     * @param \Http\Message\MessageFactory $messageFactory
     */
    public function __construct($userId, $password, HttpClient $httpClient = null, MessageFactory $messageFactory = null)
    {
        $this->userId = $userId;
        $this->password = $password;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * https.
     *
     * @return $this
     */
    public function https()
    {
        $this->apiEndpoint = 'https://oms.every8d.com/API21/HTTP/';

        return $this;
    }

    /**
     * send.
     *
     * @param array $params
     * @return string
     */
    public function send($params)
    {
        $response = $this->doRequest('sendSMS.ashx', array_filter(array_merge([
            'UID' => $this->userId,
            'PWD' => $this->password,
            'SB' => null,
            'MSG' => null,
            'DEST' => null,
            'ST' => null,
            'RETRYTIME' => null,
        ], $this->remapParams($params))));

        if ($this->isValidResponse($response) === false) {
            throw new DomainException($response, 500);
        }

        return $this->parseResponse($response);
    }

    /**
     * sendMMS.
     *
     * @param array $params
     * @return string
     */
    public function sendMMS($params)
    {
        $response = $this->https()->doRequest('snedMMS.ashx', array_filter(array_merge([
            'UID' => $this->userId,
            'PWD' => $this->password,
            'SB' => null,
            'MSG' => null,
            'DEST' => null,
            'ST' => null,
            'RETRYTIME' => null,
            'ATTACHMENT' => null,
            'TYPE' => null,
        ], $this->remapParams($params))));

        if ($this->isValidResponse($response) === false) {
            throw new DomainException($response, 500);
        }

        return $this->parseResponse($response);
    }

    /**
     * credit.
     *
     * @return float
     */
    public function credit()
    {
        if (is_null($this->credit) === false) {
            return $this->credit;
        }

        $response = $this->doRequest('getCredit.ashx', [
            'UID' => $this->userId,
            'PWD' => $this->password,
        ]);

        if ($this->isValidResponse($response) === false) {
            throw new DomainException($response, 500);
        }

        return $this->setCredit($response)->credit;
    }

    /**
     * setCredit.
     *
     * @param string $credit
     */
    protected function setCredit($credit)
    {
        $this->credit = (float) $credit;

        return $this;
    }

    /**
     * isValidResponse.
     *
     * @param string $response
     *
     * @return bool
     */
    protected function isValidResponse($response)
    {
        return substr($response, 0, 1) !== '-';
    }

    /**
     * doRequest.
     *
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    protected function doRequest($uri, $params)
    {
        $request = $this->messageFactory->createRequest(
            'POST',
            rtrim($this->apiEndpoint, '/').'/'.$uri,
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($params)
        );
        $response = $this->httpClient->sendRequest($request);

        return $response->getBody()->getContents();
    }

    /**
     * remapParams.
     *
     * @param array $params
     * @return array
     */
    protected function remapParams($params)
    {
        if (empty($params['subject']) === false) {
            $params['SB'] = $params['subject'];
            unset($params['subject']);
        }

        if (empty($params['to']) === false) {
            $params['DEST'] = $params['to'];
            unset($params['to']);
        }

        if (empty($params['text']) === false) {
            $params['MSG'] = $params['text'];
            unset($params['text']);
        }

        if (empty($params['sendTime']) === false) {
            $params['ST'] = empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null;
            unset($params['sendTime']);
        }

        if (empty($params['attachment']) === false) {
            $params['ATTACHMENT'] = $params['attachment'];
            unset($params['attachment']);
        }

        if (empty($params['type']) === false) {
            $params['TYPE'] = $params['type'];
            unset($params['type']);
        }

        return $params;
    }

    /**
     * parseResponse.
     *
     * @param array $response
     * @return array
     */
    protected function parseResponse($response)
    {
        list($credit, $sended, $cost, $unsend, $batchId) = explode(',', $response);

        return [
            'credit' => $this->setCredit($credit)->credit,
            'sended' => (int) $sended,
            'cost' => (float) $cost,
            'unsend' => (int) $unsend,
            'batchId' => $batchId,
        ];
    }
}
