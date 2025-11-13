<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;
use webignition\HttpHistoryContainer\RedirectLoopDetector;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\HttpHistoryContainer\Transaction\HttpTransactionInterface;

class RedirectLoopDetectorTest extends TestCase
{
    #[DataProvider('hasRedirectLoopDataProvider')]
    public function testHasRedirectLoop(RedirectLoopDetector $redirectLoopDetector, bool $expectedHasRedirectLoop): void
    {
        $this->assertEquals($expectedHasRedirectLoop, $redirectLoopDetector->hasRedirectLoop());
    }

    /**
     * @return array<mixed>
     */
    public static function hasRedirectLoopDataProvider(): array
    {
        return [
            'single 200 response' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        self::createResponse(200),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'contains non-redirect response (200)' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        self::createResponse(200),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'contains non-redirect response (404)' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        \Mockery::mock(RequestInterface::class),
                        self::createResponse(404),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'only redirects, no loop (all different)' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/1'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'method change within apparent loop is not loop' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'redirecting directly back to self' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/1'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self (with method group change)' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/1'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/1'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self(with method group change, loop in first group only)' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/1'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/1'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/2'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self(with method group change, loop in second group only)' => [
                'redirectLoopDetector' => new RedirectLoopDetector(self::createCollection([
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/1'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('HEAD', 'http://example.com/2'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/1'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                    new HttpTransaction(
                        self::createRequest('GET', 'http://example.com/'),
                        self::createResponse(301),
                        null,
                        []
                    ),
                ])),
                'expectedHasRedirectLoop' => true,
            ],
        ];
    }

    /**
     * @param HttpTransactionInterface[] $transactions
     */
    private static function createCollection(array $transactions): HttpTransactionCollection
    {
        $collection = new HttpTransactionCollection();

        foreach ($transactions as $transaction) {
            $collection->add($transaction);
        }

        return $collection;
    }

    private static function createResponse(int $statusCode): ResponseInterface
    {
        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->andReturn($statusCode)
        ;

        return $response;
    }

    private static function createRequest(string $method, string $url): RequestInterface
    {
        $uri = \Mockery::mock(UriInterface::class);
        $uri
            ->shouldReceive('__toString')
            ->andReturn($url)
        ;

        $request = \Mockery::mock(RequestInterface::class);

        $request
            ->shouldReceive('getMethod')
            ->andReturn($method)
        ;

        $request
            ->shouldReceive('getUri')
            ->andReturn($uri)
        ;

        return $request;
    }
}
