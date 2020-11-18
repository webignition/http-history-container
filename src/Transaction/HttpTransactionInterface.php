<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Transaction;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\InvalidTransactionException;

interface HttpTransactionInterface
{
    public const KEY_REQUEST = 'request';
    public const KEY_RESPONSE = 'response';
    public const KEY_ERROR = 'error';
    public const KEY_OPTIONS = 'options';

    /**
     * @param array<mixed> $data
     *
     * @return HttpTransactionInterface
     *
     * @throws InvalidTransactionException
     */
    public static function fromArray(array $data): HttpTransactionInterface;

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
