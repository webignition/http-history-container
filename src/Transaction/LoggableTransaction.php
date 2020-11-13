<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Transaction;

use webignition\HttpHistoryContainer\Message\LoggableRequest;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableTransaction implements \JsonSerializable
{
    public const KEY_REQUEST = 'request';
    public const KEY_RESPONSE = 'response';
    public const KEY_PERIOD = 'period';

    private const DEFAULT_EMPTY_REQUEST_DATA = [];
    private const DEFAULT_EMPTY_RESPONSE_DATA = [];

    private HttpTransaction $transaction;
    private int $period;

    public function __construct(HttpTransaction $transaction, int $period)
    {
        $this->transaction = $transaction;
        $this->period = $period;
    }

    public static function fromJson(string $transaction): self
    {
        $data = json_decode($transaction, true);

        $requestData = $data[self::KEY_REQUEST] ?? self::DEFAULT_EMPTY_REQUEST_DATA;
        if (!is_array($requestData)) {
            $requestData = self::DEFAULT_EMPTY_REQUEST_DATA;
        }

        $responseData = $data[self::KEY_RESPONSE] ?? self::DEFAULT_EMPTY_RESPONSE_DATA;
        if (!is_array($responseData)) {
            $responseData = self::DEFAULT_EMPTY_RESPONSE_DATA;
        }

        $loggableRequest = LoggableRequest::fromJson((string) json_encode($requestData));
        $loggableResponse = LoggableResponse::fromJson((string) json_encode($responseData));
        $period = (int) ($data[self::KEY_PERIOD] ?? 0);

        return new LoggableTransaction(
            new HttpTransaction(
                $loggableRequest->getRequest(),
                $loggableResponse->getResponse(),
                null,
                []
            ),
            $period
        );
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
            self::KEY_PERIOD => $this->period,
        ];
    }
}
