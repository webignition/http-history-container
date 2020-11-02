<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use webignition\HttpHistoryContainer\Message\LoggableRequest;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableTransaction implements \JsonSerializable
{
    public const KEY_REQUEST = 'request';
    public const KEY_RESPONSE = 'response';

    private const DEFAULT_EMPTY_REQUEST_DATA = [];
    private const DEFAULT_EMPTY_RESPONSE_DATA = [];

    private HttpTransaction $transaction;

    public function __construct(HttpTransaction $transaction)
    {
        $this->transaction = $transaction;
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

        return new LoggableTransaction(
            new HttpTransaction(
                $loggableRequest->getRequest(),
                $loggableResponse->getResponse(),
                null,
                []
            )
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
        ];
    }
}
