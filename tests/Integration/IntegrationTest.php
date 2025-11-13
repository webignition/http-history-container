<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use webignition\HttpHistoryContainer\Container as HttpHistoryContainer;
use webignition\HttpHistoryContainer\MiddlewareFactory;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

class IntegrationTest extends TestCase
{
    private MockHandler $mockHandler;

    private HttpClient $httpClient;

    private HttpHistoryContainer $httpHistoryContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);

        $this->httpHistoryContainer = new HttpHistoryContainer();
        $handlerStack->push(MiddlewareFactory::create($this->httpHistoryContainer));

        $this->httpClient = new HttpClient(['handler' => $handlerStack]);
    }

    public function testTransactionIsRecorded(): void
    {
        $request = new Request('GET', 'http://example.com/');
        $response = new Response();

        $this->mockHandler->append($response);

        $receivedResponse = $this->httpClient->send($request);

        $transaction = $this->httpHistoryContainer->getTransactions()->get(0);
        \assert($transaction instanceof HttpTransaction);

        self::assertRequestsAreEqual($request, $transaction->getRequest());
        self::assertSame($receivedResponse, $transaction->getResponse());
        self::assertSame($response, $transaction->getResponse());
    }

    private static function assertRequestsAreEqual(RequestInterface $expected, RequestInterface $actual): void
    {
        self::assertSame($expected->getProtocolVersion(), $actual->getProtocolVersion());
        $actualHeaders = $actual->getHeaders();
        unset($actualHeaders['User-Agent']);

        self::assertEquals($expected->getHeaders(), $actualHeaders);
        self::assertEquals($expected->getBody()->getContents(), $actual->getBody()->getContents());

        self::assertSame($expected->getRequestTarget(), $actual->getRequestTarget());
        self::assertSame($expected->getMethod(), $actual->getMethod());
        self::assertSame($expected->getUri(), $actual->getUri());
    }
}
