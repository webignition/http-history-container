<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Transaction;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Message\LoggableRequest;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableTransaction implements \JsonSerializable, HttpTransactionInterface, WithPeriodInterface
{
    public function __construct(private HttpTransaction $transaction, private int $period)
    {
    }

    public function getRequest(): RequestInterface
    {
        return $this->transaction->getRequest();
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->transaction->getResponse();
    }

    public function getError()
    {
        return $this->transaction->getError();
    }

    public function getOptions(): array
    {
        return $this->transaction->getOptions();
    }

    public function getPeriod(): int
    {
        return $this->period;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'request' => new LoggableRequest($this->transaction->getRequest()),
            'response' => new LoggableResponse($this->transaction->getResponse()),
            'period' => $this->period,
        ];
    }
}
