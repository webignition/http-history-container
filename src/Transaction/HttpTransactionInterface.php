<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Transaction;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpTransactionInterface
{
    public function getRequest(): RequestInterface;

    public function getResponse(): ?ResponseInterface;

    /**
     * @return mixed
     */
    public function getError();

    /**
     * @return array<mixed>
     */
    public function getOptions(): array;
}
