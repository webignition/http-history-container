<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpTransaction
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
