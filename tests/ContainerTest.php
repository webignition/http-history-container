<?php

namespace webignition\HttpHistoryContainer\Tests;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = new Container();
    }

    /**
     * @dataProvider invalidOffsetDataProvider
     *
     * @param mixed $offset
     */
    public function testOffsetSetInvalidOffset($offset)
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
        $expectedExceptionMessage,
        $expectedExceptionCode
    ) {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->expectExceptionCode($expectedExceptionCode);

        $this->container->offsetSet(null, $httpTransaction);
    }

    /**
     * @dataProvider arrayAccessOffsetSetOffsetGetDataProvider
     *
     * @param array $existingHttpTransactions
     * @param mixed $offsetSetOffset
     * @param array $offsetSetHttpTransaction
     * @param mixed $offsetGetOffset
     * @param array $expectedHttpTransaction
     */
    public function testArrayAccessOffsetSetOffsetGet(
        array $existingHttpTransactions,
        $offsetSetOffset,
        $offsetSetHttpTransaction,
        $offsetGetOffset,
        $expectedHttpTransaction
    ) {
        foreach ($existingHttpTransactions as $existingOffset => $existingTransaction) {
            $this->container->offsetSet($existingOffset, $existingTransaction);
        }

        $this->container->offsetSet($offsetSetOffset, $offsetSetHttpTransaction);
        $this->assertEquals($expectedHttpTransaction, $this->container->offsetGet($offsetGetOffset));
    }

    /**
     * @return array
     */
    public function arrayAccessOffsetSetOffsetGetDataProvider()
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

    public function testArrayAccessOffsetExistsOffsetUnset()
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

    public function testGetRequests()
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

    public function testGetResponses()
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

    public function testGetRequestUrls()
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

    public function testGetRequestUrlsAsStrings()
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

    public function testGetLastRequest()
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

    public function testGetLastRequestUrl()
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

    /**
     * @return array
     */
    public function invalidOffsetDataProvider()
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

    /**
     * @return array
     */
    public function invalidHttpTransactionDataProvider()
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

    public function testGetLastResponse()
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

    public function testRequestCanBeNull()
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
}
