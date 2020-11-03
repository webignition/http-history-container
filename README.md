# Guzzle HTTP History Container

Helping you more easily test what your [Guzzle HTTP client](https://docs.guzzlephp.org/en/stable/) has been up to.

A container for [Guzzle history middleware](https://docs.guzzlephp.org/en/stable/testing.html#history-middleware)
offering smashingly-nice access to:
 
- collections of HTTP transactions (requests plus responses)
- requests made
- responses received
- request URLs 

Oh also logging. Really useful when your Guzzle client under test is not executing within the same thread as your tests.

## Basic Usage

```php
<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Container;

$historyContainer = new Container();
$historyHandler = Middleware::history($historyContainer);
$handlerStack = HandlerStack::create($historyHandler);
$httpClient = new HttpClient(['handler' => $handlerStack]);

/// ... things happen ...

$historyContainer->getTransactions();
// an array of HttpTransaction

$historyContainer->getRequests();
// a Collection\RequestCollection

$historyContainer->getResponses();
// a Collection\ResponseCollection

foreach ($historyContainer as $transaction) {
    // $transaction is a HttpTransaction

    $request = $transaction->getRequest();
    // $request is a RequestInterface

    $response = $transaction->getResponse();
    // $response is a ResponseInterface
}
```
