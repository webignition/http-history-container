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
    public function getRequests();

    /**
     * @return ResponseInterface[]
     */
    public function getResponses();

    /**
     * @return UriInterface[]
     */
    public function getRequestUrls();

    /**
     * @return string[]
     */
    public function getRequestUrlsAsStrings();

    /**
     * @return RequestInterface
     */
    public function getLastRequest();

    /**
     * @return UriInterface
     */
    public function getLastRequestUrl();
}
