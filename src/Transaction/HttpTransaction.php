<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Transaction;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\InvalidTransactionException;

class HttpTransaction implements HttpTransactionInterface
{
    private RequestInterface $request;
    private ?ResponseInterface $response;

    /**
     * @var mixed
     */
    private $error;

    /**
     * @var array<mixed>
     */
    private array $options;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param mixed $error
     * @param array<mixed> $options
     */
    public function __construct(
        RequestInterface $request,
        ?ResponseInterface $response,
        $error,
        array $options
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->error = $error;
        $this->options = $options;
    }

    /**
     * @param array<mixed> $data
     *
     * @return HttpTransaction
     *
     * @throws InvalidTransactionException
     */
    public static function fromArray(array $data): HttpTransaction
    {
        $request = $data[HttpTransactionInterface::KEY_REQUEST] ?? null;
        $response = $data[HttpTransactionInterface::KEY_RESPONSE] ?? null;
        $error = $data[HttpTransactionInterface::KEY_ERROR] ?? null;
        $options = $data[HttpTransactionInterface::KEY_OPTIONS] ?? [];

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
