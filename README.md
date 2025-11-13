# Guzzle HTTP History Container

Helping you more easily test what your [Guzzle HTTP client](https://docs.guzzlephp.org/en/stable/) has been up to.

A container for [Guzzle history middleware](https://docs.guzzlephp.org/en/stable/testing.html#history-middleware)
offering smashingly-nice access to:
 
- collections of HTTP transactions (requests plus responses)
- requests made
- responses received
- request URLs 

Oh also logging. Really useful when your Guzzle client under test is not executing within the same thread as your tests.

## Usage

### Basic Usage

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

### Details on Requests Sent

```php
<?php

use webignition\HttpHistoryContainer\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

$historyContainer = new Container();

// ... create and use Guzzle client ...

$transactions = $historyContainer->getTransactions();
// $transactions is a Collection\TransactionCollection

$requests = $transactions->getRequests();
// $requests is a Collection\RequestCollection

// RequestCollection is iterable
foreach ($requests as $request) {
    // $request is a RequestInterface
}

$urls = $requests->getUrls();
// $urls is a Collection\UrlCollection

// UrlCollection is iterable
foreach ($urls as $url) {
    // $url is a UriInterface
}

foreach ($urls->getAsStrings() as $urlString) {
    // convenient access to requested URLs as strings
}
```

### Details on Responses Received

```php
<?php

use webignition\HttpHistoryContainer\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

$historyContainer = new Container();

// ... create and use Guzzle client ...

$transactions = $historyContainer->getTransactions();
// $transactions is a Collection\TransactionCollection

$responses = $transactions->getResponses();
// $responses is a Collection\ResponseCollection

// ResponseCollection is iterable
foreach ($responses as $response) {
    // $response is a ResponseInterface
}
```

## Logging!

You may not be able to access your Guzzle history if the client under test is executing
in a different thread from your tests.

A `LoggableContainer` allows you to record transactions as they happen to whatever `Psr\Log\LoggerInterface`
instance you like. 

The logging container wraps each `HttpTransaction` in a `LoggableTransaction` object which is
serialized to JSON and output as a debug message.

`LoggableTransactionFactory::createFromJson()` lets you (in a somewhat slightly lossy manner) re-create 
transactions object from logged messages.`

### Logging Example

```php
<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use webignition\HttpHistoryContainer\LoggableContainer;
use webignition\HttpHistoryContainer\Factory\LoggableTransactionFactory;

// Create the PSR logger
$path = '/path/to/log';
$stream = fopen($path, 'w+');

$logger = new Logger('');
$logHandler = new StreamHandler($stream);
$logHandler
    ->setFormatter(new LineFormatter('%message%' . "\n"));

$logger->pushHandler($logHandler);

// Create LoggableContainer
$container = new LoggableContainer($logger);

// ... create and use Guzzle client ...

$logContent = file_get_contents($path);
$logLines = array_filter(explode("\n", $logContent));

$loggedTransactions = [];
foreach ($logLines as $logLine) {
    $loggedTransactions[] = LoggableTransactionFactory::createFromJson($logLine);
}

// $loggedTransactions is now an array of LoggableTransaction
foreach ($loggedTransactions as $loggedTransaction) {
    $transaction = $loggedTransaction->getTransaction();    
    // $transaction is a HttpTransaction

    $request = $transaction->getRequest();
    // $request is a RequestInterface

    $response = $transaction->getResponse();
    // $response is a ResponseInterface
}
```
