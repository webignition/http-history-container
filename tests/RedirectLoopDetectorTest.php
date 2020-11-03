<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\Container;
use webignition\HttpHistoryContainer\RedirectLoopDetector;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

class RedirectLoopDetectorTest extends TestCase
{
    /**
     * @dataProvider hasRedirectLoopDataProvider
     */
    public function testHasRedirectLoop(RedirectLoopDetector $redirectLoopDetector, bool $expectedHasRedirectLoop)
    {
        $this->assertEquals($expectedHasRedirectLoop, $redirectLoopDetector->hasRedirectLoop());
    }

    public function hasRedirectLoopDataProvider(): array
    {
        return [
            'single 200 response' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(200),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'contains non-redirect response (200)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(200),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'contains non-redirect response (404)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(404),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'only redirects, no loop (all different)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => false,
            ],
            'method change within apparent loop is not loop' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                 'expectedHasRedirectLoop' => false,
            ],
            'redirecting directly back to self' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self (with method group change)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/1'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self(with method group change, loop in first group only)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/1'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/2'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self(with method group change, loop in second group only)' => [
                'redirectLoopDetector' => new RedirectLoopDetector($this->createContainer([
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/1'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/2'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                    [
                        HttpTransaction::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        HttpTransaction::KEY_RESPONSE => $this->createResponse(301),
                        HttpTransaction::KEY_ERROR => null,
                        HttpTransaction::KEY_OPTIONS => []
                    ],
                ])),
                'expectedHasRedirectLoop' => true,
            ],
        ];
    }

    /**
     * @param array<mixed> $transactions
     *
     * @return Container
     */
    private function createContainer(array $transactions): Container
    {
        $container = new Container();

        foreach ($transactions as $transaction) {
            $container[] = $transaction;
        }

        return $container;
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
