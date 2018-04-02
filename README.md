# http-history-container

A collection of convenience methods for getting requests and responses from a container used by the Guzzle history middleware.

## Usage

```php
<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use webignition\HttpHistoryContainer\Container;

$historycontainer = new Container();
$historyHandler = Middleware::history($historycontainer);
$handlerStack = HandlerStack::create($historyHandler);
$httpClient = new HttpClient(['handler' => $handlerStack]);
```

## Method overview

```php
<?php

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

    /**
     * @return ResponseInterface
     */
    public function getLastResponse();
}
```
