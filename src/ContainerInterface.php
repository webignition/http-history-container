<?php

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface ContainerInterface
{
    /**
     * @return RequestInterface[]
     */
    public function getRequests(): array;

    /**
     * @return ResponseInterface[]
     */
    public function getResponses(): array;

    /**
     * @return UriInterface[]
     */
    public function getRequestUrls(): array;

    /**
     * @return string[]
     */
    public function getRequestUrlsAsStrings(): array;

    public function getLastRequest(): ?RequestInterface;
    public function getLastRequestUrl(): ?UriInterface;
    public function getLastResponse(): ?ResponseInterface;
}
