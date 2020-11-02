<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use webignition\HttpHistoryContainer\Message\LoggableRequest;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableTransaction implements \JsonSerializable
{
    public const KEY_REQUEST = 'request';
    public const KEY_RESPONSE = 'response';

    private HttpTransaction $transaction;

    public function __construct(HttpTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function getTransaction(): HttpTransaction
    {
        return $this->transaction;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            self::KEY_REQUEST => new LoggableRequest($this->transaction->getRequest()),
            self::KEY_RESPONSE => new LoggableResponse($this->transaction->getResponse()),
        ];
    }
}
