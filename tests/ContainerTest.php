<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\Container;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    /**
     * @dataProvider invalidOffsetDataProvider
     *
     * @param mixed $offset
     */
    public function testOffsetSetInvalidOffset($offset): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Container::OFFSET_INVALID_MESSAGE);
        $this->expectExceptionCode(Container::OFFSET_INVALID_CODE);

        $this->container->offsetSet($offset, null);
    }

    /**
     * @dataProvider invalidHttpTransactionDataProvider
     *
     * @param mixed $httpTransaction
     * @param string $expectedExceptionMessage
     * @param int $expectedExceptionCode
     */
    public function testOffsetSetInvalidHttpTransaction(
        $httpTransaction,
        string $expectedExceptionMessage,
        int $expectedExceptionCode
    ): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->expectExceptionCode($expectedExceptionCode);

        $this->container->offsetSet(null, $httpTransaction);
    }

    /**
     * @dataProvider arrayAccessOffsetSetOffsetGetDataProvider
     *
     * @param array<int, array<int, RequestInterface|ResponseInterface>> $existingHttpTransactions
     * @param mixed $offsetSetOffset
     * @param array<int, array<int, RequestInterface|ResponseInterface>> $offsetSetHttpTransaction
     * @param mixed $offsetGetOffset
     * @param array<int, RequestInterface|ResponseInterface>|null $expectedHttpTransaction
     */
    public function testArrayAccessOffsetSetOffsetGet(
        array $existingHttpTransactions,
        $offsetSetOffset,
        array $offsetSetHttpTransaction,
        $offsetGetOffset,
        ?array $expectedHttpTransaction
    ) {
        foreach ($existingHttpTransactions as $existingOffset => $existingTransaction) {
            $this->container->offsetSet($existingOffset, $existingTransaction);
        }

        $this->container->offsetSet($offsetSetOffset, $offsetSetHttpTransaction);
        $this->assertEquals($expectedHttpTransaction, $this->container->offsetGet($offsetGetOffset));
    }

    public function arrayAccessOffsetSetOffsetGetDataProvider(): array
    {
        $httpTransaction0 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => [
                'value_0_options_key' => 'value_0_options_value',
            ]
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => [
                'value_1_options_key' => 'value_1_options_value',
            ]
        ];

        $existingHttpTransactions = [
            $httpTransaction0,
        ];

        return [
            'no existing values; offsetSetOffset=null, offsetGetOffset=null' => [
                'existingHttpTransactions' => [],
                'offsetSetOffset' => null,
                'offsetSetHttpTransaction' => $httpTransaction0,
                'offsetGetOffset' => null,
                'expectedHttpTransaction' => null,
            ],
            'no existing values; offsetSetOffset=null, offsetGetOffset=0' => [
                'existingHttpTransactions' => [],
                'offsetSetOffset' => null,
                'offsetSetHttpTransaction' => $httpTransaction0,
                'offsetGetOffset' => 0,
                'expectedHttpTransaction' => $httpTransaction0,
            ],
            'no existing values; offsetSetOffset=null, offsetGetOffset=1' => [
                'existingHttpTransactions' => [],
                'offsetSetOffset' => null,
                'offsetSetHttpTransaction' => $httpTransaction0,
                'offsetGetOffset' => 1,
                'expectedHttpTransaction' => null,
            ],
            'no existing values; offsetSetOffset=1, offsetGetOffset=null' => [
                'existingHttpTransactions' => [],
                'offsetSetOffset' => 1,
                'offsetSetHttpTransaction' => $httpTransaction0,
                'offsetGetOffset' => null,
                'expectedHttpTransaction' => null,
            ],
            'no existing values; offsetSetOffset=1, offsetGetOffset=0' => [
                'existingHttpTransactions' => [],
                'offsetSetOffset' => 1,
                'offsetSetHttpTransaction' => $httpTransaction0,
                'offsetGetOffset' => 0,
                'expectedHttpTransaction' => null,
            ],
            'no existing values; offsetSetOffset=1, offsetGetOffset=1' => [
                'existingHttpTransactions' => [],
                'offsetSetOffset' => 1,
                'offsetSetHttpTransaction' => $httpTransaction0,
                'offsetGetOffset' => 1,
                'expectedHttpTransaction' => $httpTransaction0,
            ],
            'has existing values; offsetSetOffset=null, offsetGetOffset=null' => [
                'existingHttpTransactions' => $existingHttpTransactions,
                'offsetSetOffset' => null,
                'offsetSetHttpTransaction' => $httpTransaction1,
                'offsetGetOffset' => null,
                'expectedHttpTransaction' => null,
            ],
            'has existing values; offsetSetOffset=null, offsetGetOffset=0' => [
                'existingHttpTransactions' => $existingHttpTransactions,
                'offsetSetOffset' => null,
                'offsetSetHttpTransaction' => $httpTransaction1,
                'offsetGetOffset' => 0,
                'expectedHttpTransaction' => $httpTransaction0,
            ],
            'has existing values; offsetSetOffset=null, offsetGetOffset=1' => [
                'existingHttpTransactions' => $existingHttpTransactions,
                'offsetSetOffset' => null,
                'offsetSetHttpTransaction' => $httpTransaction1,
                'offsetGetOffset' => 1,
                'expectedHttpTransaction' => $httpTransaction1,
            ],
            'has existing values; offsetSetOffset=1, offsetGetOffset=null' => [
                'existingHttpTransactions' => $existingHttpTransactions,
                'offsetSetOffset' => 1,
                'offsetSetHttpTransaction' => $httpTransaction1,
                'offsetGetOffset' => null,
                'expectedHttpTransaction' => null,
            ],
            'has existing values; offsetSetOffset=1, offsetGetOffset=0' => [
                'existingHttpTransactions' => $existingHttpTransactions,
                'offsetSetOffset' => 1,
                'offsetSetHttpTransaction' => $httpTransaction1,
                'offsetGetOffset' => 0,
                'expectedHttpTransaction' => $httpTransaction0,
            ],
            'has existing values; offsetSetOffset=1, offsetGetOffset=1' => [
                'existingHttpTransactions' => $existingHttpTransactions,
                'offsetSetOffset' => 1,
                'offsetSetHttpTransaction' => $httpTransaction1,
                'offsetGetOffset' => 1,
                'expectedHttpTransaction' => $httpTransaction1,
            ],
            'has existing values; offsetSetOffset=0, offsetGetOffset=0' => [
                'existingHttpTransactions' => $existingHttpTransactions,
                'offsetSetOffset' => 0,
                'offsetSetHttpTransaction' => $httpTransaction1,
                'offsetGetOffset' => 0,
                'expectedHttpTransaction' => $httpTransaction1,
            ],
        ];
    }

    public function testArrayAccessOffsetExistsOffsetUnset(): void
    {
        $httpTransaction = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertFalse($this->container->offsetExists(0));

        $this->container->offsetSet(0, $httpTransaction);
        $this->assertTrue($this->container->offsetExists(0));

        $this->container->offsetUnset(0);
        $this->assertFalse($this->container->offsetExists(0));
    }

    public function testGetRequests(): void
    {
        $httpTransaction0Request = \Mockery::mock(RequestInterface::class);
        $httpTransaction1Request = \Mockery::mock(RequestInterface::class);

        $httpTransaction0 = [
            Container::KEY_REQUEST => $httpTransaction0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => $httpTransaction1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getRequests());

        $this->container[] = $httpTransaction0;
        $this->container[] = $httpTransaction1;

        $this->assertEquals(
            [
                $httpTransaction0Request,
                $httpTransaction1Request,
            ],
            $this->container->getRequests()
        );
    }

    public function testGetResponses(): void
    {
        $httpTransaction0Response = \Mockery::mock(ResponseInterface::class);
        $httpTransaction1Response = \Mockery::mock(ResponseInterface::class);

        $httpTransaction0 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => $httpTransaction0Response,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => $httpTransaction1Response,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getResponses());

        $this->container[] = $httpTransaction0;
        $this->container[] = $httpTransaction1;

        $this->assertEquals(
            [
                $httpTransaction0Response,
                $httpTransaction1Response,
            ],
            $this->container->getResponses()
        );
    }

    public function testGetRequestUrls(): void
    {
        $httpTransaction0RequestUri = \Mockery::mock(UriInterface::class);
        $httpTransaction0RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/0/');

        $httpTransaction1RequestUri = \Mockery::mock(UriInterface::class);
        $httpTransaction1RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/1/');

        $httpTransaction0Request = \Mockery::mock(RequestInterface::class);
        $httpTransaction0Request
            ->shouldReceive('getUri')
            ->andReturn($httpTransaction0RequestUri);

        $httpTransaction1Request = \Mockery::mock(RequestInterface::class);
        $httpTransaction1Request
            ->shouldReceive('getUri')
            ->andReturn($httpTransaction1RequestUri);

        $httpTransaction0 = [
            Container::KEY_REQUEST => $httpTransaction0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => $httpTransaction1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getRequestUrls());

        $this->container[] = $httpTransaction0;
        $this->container[] = $httpTransaction1;

        $this->assertEquals(
            [
                $httpTransaction0RequestUri,
                $httpTransaction1RequestUri,
            ],
            $this->container->getRequestUrls()
        );
    }

    public function testGetRequestUrlsAsStrings(): void
    {
        $httpTransaction0RequestUri = \Mockery::mock(UriInterface::class);
        $httpTransaction0RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/0/');

        $httpTransaction1RequestUri = \Mockery::mock(UriInterface::class);
        $httpTransaction1RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/1/');

        $httpTransaction0Request = \Mockery::mock(RequestInterface::class);
        $httpTransaction0Request
            ->shouldReceive('getUri')
            ->andReturn($httpTransaction0RequestUri);

        $httpTransaction1Request = \Mockery::mock(RequestInterface::class);
        $httpTransaction1Request
            ->shouldReceive('getUri')
            ->andReturn($httpTransaction1RequestUri);

        $httpTransaction0 = [
            Container::KEY_REQUEST => $httpTransaction0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => $httpTransaction1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getRequestUrlsAsStrings());

        $this->container[] = $httpTransaction0;
        $this->container[] = $httpTransaction1;

        $this->assertEquals(
            [
                'http://example.com/0/',
                'http://example.com/1/',
            ],
            $this->container->getRequestUrlsAsStrings()
        );
    }

    public function testGetLastRequest(): void
    {
        $httpTransaction0Request = \Mockery::mock(RequestInterface::class);
        $httpTransaction1Request = \Mockery::mock(RequestInterface::class);

        $httpTransaction0 = [
            Container::KEY_REQUEST => $httpTransaction0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => $httpTransaction1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getLastRequest());

        $this->container[] = $httpTransaction0;
        $this->container[] = $httpTransaction1;

        $this->assertEquals($httpTransaction1Request, $this->container->getLastRequest());
    }

    public function testGetLastRequestUrl(): void
    {
        $httpTransaction1RequestUri = \Mockery::mock(UriInterface::class);
        $httpTransaction1RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/1/');

        $httpTransaction0Request = \Mockery::mock(RequestInterface::class);

        $httpTransaction1Request = \Mockery::mock(RequestInterface::class);
        $httpTransaction1Request
            ->shouldReceive('getUri')
            ->andReturn($httpTransaction1RequestUri);

        $httpTransaction0 = [
            Container::KEY_REQUEST => $httpTransaction0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => $httpTransaction1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getLastRequestUrl());

        $this->container[] = $httpTransaction0;
        $this->container[] = $httpTransaction1;

        $this->assertEquals($httpTransaction1RequestUri, $this->container->getLastRequestUrl());
    }

    public function invalidOffsetDataProvider(): array
    {
        return [
            'bool' => [
                'offset' => true,
            ],
            'string' => [
                'offset' => 'foo',
            ],
        ];
    }

    public function invalidHttpTransactionDataProvider(): array
    {
        return [
            'not an array' => [
                'httpTransaction' => null,
                'expectedExceptionMessage' => Container::VALUE_NOT_ARRAY_MESSAGE,
                'expectedExceptionCode' => Container::VALUE_NOT_ARRAY_CODE,
            ],
            'missing request key' => [
                'httpTransaction' => [],
                'expectedExceptionMessage' => 'Key "request" must be present',
                'expectedExceptionCode' => Container::VALUE_MISSING_KEY_CODE,
            ],
            'missing response key' => [
                'httpTransaction' => [
                    Container::KEY_REQUEST => null,
                ],
                'expectedExceptionMessage' => 'Key "response" must be present',
                'expectedExceptionCode' => Container::VALUE_MISSING_KEY_CODE,
            ],
            'missing error key' => [
                'httpTransaction' => [
                    Container::KEY_REQUEST => null,
                    Container::KEY_RESPONSE => null,
                ],
                'expectedExceptionMessage' => 'Key "error" must be present',
                'expectedExceptionCode' => Container::VALUE_MISSING_KEY_CODE,
            ],
            'missing options key' => [
                'httpTransaction' => [
                    Container::KEY_REQUEST => null,
                    Container::KEY_RESPONSE => null,
                    Container::KEY_ERROR => null,
                ],
                'expectedExceptionMessage' => 'Key "options" must be present',
                'expectedExceptionCode' => Container::VALUE_MISSING_KEY_CODE,
            ],
            'request not a RequestInterface' => [
                'httpTransaction' => [
                    Container::KEY_REQUEST => null,
                    Container::KEY_RESPONSE => null,
                    Container::KEY_ERROR => null,
                    Container::KEY_OPTIONS => null,
                ],
                'expectedExceptionMessage' => Container::VALUE_REQUEST_NOT_REQUEST_MESSAGE,
                'expectedExceptionCode' => Container::VALUE_REQUEST_NOT_REQUEST_CODE,
            ],
            'response not a ResponseInterface' => [
                'httpTransaction' => [
                    Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                    Container::KEY_RESPONSE => new \stdClass(),
                    Container::KEY_ERROR => null,
                    Container::KEY_OPTIONS => null,
                ],
                'expectedExceptionMessage' => Container::VALUE_RESPONSE_NOT_RESPONSE_MESSAGE,
                'expectedExceptionCode' => Container::VALUE_RESPONSE_NOT_RESPONSE_CODE,
            ],
        ];
    }

    public function testGetLastResponse(): void
    {
        $httpTransaction0Response = \Mockery::mock(ResponseInterface::class);
        $httpTransaction1Response = \Mockery::mock(ResponseInterface::class);

        $httpTransaction0 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => $httpTransaction0Response,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => $httpTransaction1Response,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getLastResponse());

        $this->container[] = $httpTransaction0;
        $this->container[] = $httpTransaction1;

        $this->assertEquals($httpTransaction1Response, $this->container->getLastResponse());
    }

    public function testRequestCanBeNull(): void
    {
        $httpTransaction = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => null,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->container[] = $httpTransaction;

        $this->assertEquals($httpTransaction, $this->container[0]);
    }

    public function testIterator(): void
    {
        $httpTransaction0Response = \Mockery::mock(ResponseInterface::class);
        $httpTransaction1Response = \Mockery::mock(ResponseInterface::class);

        $httpTransaction0 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => $httpTransaction0Response,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransaction1 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => $httpTransaction1Response,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $httpTransactions = [
            $httpTransaction0,
            $httpTransaction1,
        ];

        $this->container[] = $httpTransaction0;
        $this->container[] = $httpTransaction1;

        $iteratedTransactionCount = 0;

        foreach ($this->container as $httpTransactionIndex => $httpTransaction) {
            $iteratedTransactionCount++;
            $this->assertEquals($httpTransactions[$httpTransactionIndex], $httpTransaction);
        }

        $this->assertEquals(2, $iteratedTransactionCount);
    }

    public function testClear(): void
    {
        $httpTransaction = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->container[] = $httpTransaction;
        $this->assertCount(1, $this->container);

        $this->container->clear();
        $this->assertCount(0, $this->container);
    }

    /**
     * @dataProvider hasRedirectLoopDataProvider
     *
     * @param array<int, array<int, RequestInterface|ResponseInterface>> $httpTransactions
     * @param bool $expectedHasRedirectLoop
     */
    public function testHasRedirectLoop(array $httpTransactions, bool $expectedHasRedirectLoop)
    {
        foreach ($httpTransactions as $httpTransaction) {
            $this->container[] = $httpTransaction;
        }

        $this->assertEquals($expectedHasRedirectLoop, $this->container->hasRedirectLoop());
    }

    public function hasRedirectLoopDataProvider(): array
    {
        return [
            'single 200 response' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        Container::KEY_RESPONSE => $this->createResponse(200),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => false,
            ],
            'contains non-redirect response (200)' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        Container::KEY_RESPONSE => $this->createResponse(200),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => false,
            ],
            'contains non-redirect response (404)' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                        Container::KEY_RESPONSE => $this->createResponse(404),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => false,
            ],
            'only redirects, no loop (all different)' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => false,
            ],
            'method change within apparent loop is not loop' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => false,
            ],
            'redirecting directly back to self' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self (with method group change)' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/1'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self(with method group change, loop in first group only)' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/1'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/2'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => true,
            ],
            'redirecting indirectly back to self(with method group change, loop in second group only)' => [
                'httpTransactions' => [
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/1'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('HEAD', 'http://example.com/2'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/1'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                    [
                        Container::KEY_REQUEST => $this->createRequest('GET', 'http://example.com/'),
                        Container::KEY_RESPONSE => $this->createResponse(301),
                        Container::KEY_ERROR => null,
                        Container::KEY_OPTIONS => []
                    ],
                ],
                'expectedHasRedirectLoop' => true,
            ],
        ];
    }

    public function testGetTransactions(): void
    {
        $this->assertEquals([], $this->container->getTransactions());
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
