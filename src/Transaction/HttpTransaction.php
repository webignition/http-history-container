<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Transaction;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\InvalidTransactionException;

class HttpTransaction implements HttpTransactionInterface
{
    public const KEY_REQUEST = 'request';
    public const KEY_RESPONSE = 'response';
    public const KEY_ERROR = 'error';
    public const KEY_OPTIONS = 'options';

    /**
     * @param array<mixed> $options
     */
    public function __construct(
        private RequestInterface $request,
        private ?ResponseInterface $response,
        private mixed $error,
        private array $options
    ) {
    }

    /**
     * @throws InvalidTransactionException
     */
    public static function fromArray(mixed $data): HttpTransaction
    {
        if (!is_array($data)) {
            $data = [];
        }

        $request = $data[self::KEY_REQUEST] ?? null;
        $response = $data[self::KEY_RESPONSE] ?? null;
        $error = $data[self::KEY_ERROR] ?? null;
        $options = $data[self::KEY_OPTIONS] ?? [];

        if (!$request instanceof RequestInterface) {
            throw InvalidTransactionException::createForInvalidRequest($data);
        }

        if (null !== $response && !$response instanceof ResponseInterface) {
            throw InvalidTransactionException::createForInvalidResponse($data);
        }

        return new HttpTransaction($request, $response, $error, $options);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return array<mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
