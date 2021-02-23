<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;
use webignition\HttpHistoryContainer\RedirectLoopDetector;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

class RedirectLoopDetectorTest extends TestCase
{
    /**
     * @dataProvider hasRedirectLoopDataProvider
     */
    public function testHasRedirectLoop(RedirectLoopDetector $redirectLoopDetector, bool $expectedHasRedirectLoop): void
    {
        $this->assertEquals($expectedHasRedirectLoop, $redirectLoopDetector->hasRedirectLoop());
    }

    /**
     * @return array[]
     */
    public function hasRedirectLoopDataProvider(): array
    {
        return [
            'single 200 response' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        $this->createResponse(200),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'contains non-redirect response (200)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        $this->createResponse(200),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'contains non-redirect response (404)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        $this->createResponse(404),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'only redirects, no loop (all different)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/1'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'method change within apparent loop is not loop' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                ])),
                 'expectedHasRedirectLoop' => false,
            ],
            'redirecting directly back to self' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/1'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self (with method group change)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/1'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/1'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self(with method group change, loop in first group only)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/1'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/1'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/2'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self(with method group change, loop in second group only)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createCollection([
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/1'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('HEAD', 'http://example.com/2'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/1'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        $this->createRequest('GET', 'http://example.com/'),
                        $this->createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
        ];
    }

    /**
     * @param array<mixed> $transactions
     *
     * @return HttpTransactionCollection
     */
    private function createCollection(array $transactions): HttpTransactionCollection
    {
        $collection = new HttpTransactionCollection();

        foreach ($transactions as $transaction) {
            $collection->add($transaction);
        }

        return $collection;
    }

    private function createResponse(int $statusCode): ResponseInterface
    {
        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->andReturn($statusCode);

        return $response;
    }

    private function createRequest(string $method, string $url): RequestInterface
    {
        $uri = \Mockery::mock(UriInterface::class);
        $uri
            ->shouldReceive('__toString')
            ->andReturn($url);

        $request = \Mockery::mock(RequestInterface::class);

        $request
            ->shouldReceive('getMethod')
            ->andReturn($method);

        $request
            ->shouldReceive('getUri')
            ->andReturn($uri);

        return $request;
    }
}
