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
     * @dataProvider invalidValueDataProvider
     *
     * @param mixed $value
     * @param string $expectedExceptionMessage
     * @param int $expectedExceptionCode
     */
    public function testOffsetSetInvalidValue($value, $expectedExceptionMessage, $expectedExceptionCode)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->expectExceptionCode($expectedExceptionCode);

        $this->container->offsetSet(null, $value);
    }

    /**
     * @dataProvider arrayAccessOffsetSetOffsetGetDataProvider
     *
     * @param array $existingValues
     * @param mixed $offsetSetOffset
     * @param array $offsetSetValue
     * @param mixed $offsetGetOffset
     * @param array $expectedValue
     */
    public function testArrayAccessOffsetSetOffsetGet(
        array $existingValues,
        $offsetSetOffset,
        $offsetSetValue,
        $offsetGetOffset,
        $expectedValue
    ) {
        foreach ($existingValues as $existingOffset => $existingValue) {
            $this->container->offsetSet($existingOffset, $existingValue);
        }

        $this->container->offsetSet($offsetSetOffset, $offsetSetValue);
        $this->assertEquals($expectedValue, $this->container->offsetGet($offsetGetOffset));
    }

    /**
     * @return array
     */
    public function arrayAccessOffsetSetOffsetGetDataProvider()
    {
        $value0 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => [
                'value_0_options_key' => 'value_0_options_value',
            ]
        ];

        $value1 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => [
                'value_1_options_key' => 'value_1_options_value',
            ]
        ];

        $existingValues = [
            $value0,
        ];

        return [
            'no existing values; offsetSetOffset=null, offsetGetOffset=null' => [
                'existingValues' => [],
                'offsetSetOffset' => null,
                'offsetSetValue' => $value0,
                'offsetGetOffset' => null,
                'expectedValue' => null,
            ],
            'no existing values; offsetSetOffset=null, offsetGetOffset=0' => [
                'existingValues' => [],
                'offsetSetOffset' => null,
                'offsetSetValue' => $value0,
                'offsetGetOffset' => 0,
                'expectedValue' => $value0,
            ],
            'no existing values; offsetSetOffset=null, offsetGetOffset=1' => [
                'existingValues' => [],
                'offsetSetOffset' => null,
                'offsetSetValue' => $value0,
                'offsetGetOffset' => 1,
                'expectedValue' => null,
            ],
            'no existing values; offsetSetOffset=1, offsetGetOffset=null' => [
                'existingValues' => [],
                'offsetSetOffset' => 1,
                'offsetSetValue' => $value0,
                'offsetGetOffset' => null,
                'expectedValue' => null,
            ],
            'no existing values; offsetSetOffset=1, offsetGetOffset=0' => [
                'existingValues' => [],
                'offsetSetOffset' => 1,
                'offsetSetValue' => $value0,
                'offsetGetOffset' => 0,
                'expectedValue' => null,
            ],
            'no existing values; offsetSetOffset=1, offsetGetOffset=1' => [
                'existingValues' => [],
                'offsetSetOffset' => 1,
                'offsetSetValue' => $value0,
                'offsetGetOffset' => 1,
                'expectedValue' => $value0,
            ],
            'has existing values; offsetSetOffset=null, offsetGetOffset=null' => [
                'existingValues' => $existingValues,
                'offsetSetOffset' => null,
                'offsetSetValue' => $value1,
                'offsetGetOffset' => null,
                'expectedValue' => null,
            ],
            'has existing values; offsetSetOffset=null, offsetGetOffset=0' => [
                'existingValues' => $existingValues,
                'offsetSetOffset' => null,
                'offsetSetValue' => $value1,
                'offsetGetOffset' => 0,
                'expectedValue' => $value0,
            ],
            'has existing values; offsetSetOffset=null, offsetGetOffset=1' => [
                'existingValues' => $existingValues,
                'offsetSetOffset' => null,
                'offsetSetValue' => $value1,
                'offsetGetOffset' => 1,
                'expectedValue' => $value1,
            ],
            'has existing values; offsetSetOffset=1, offsetGetOffset=null' => [
                'existingValues' => $existingValues,
                'offsetSetOffset' => 1,
                'offsetSetValue' => $value1,
                'offsetGetOffset' => null,
                'expectedValue' => null,
            ],
            'has existing values; offsetSetOffset=1, offsetGetOffset=0' => [
                'existingValues' => $existingValues,
                'offsetSetOffset' => 1,
                'offsetSetValue' => $value1,
                'offsetGetOffset' => 0,
                'expectedValue' => $value0,
            ],
            'has existing values; offsetSetOffset=1, offsetGetOffset=1' => [
                'existingValues' => $existingValues,
                'offsetSetOffset' => 1,
                'offsetSetValue' => $value1,
                'offsetGetOffset' => 1,
                'expectedValue' => $value1,
            ],
            'has existing values; offsetSetOffset=0, offsetGetOffset=0' => [
                'existingValues' => $existingValues,
                'offsetSetOffset' => 0,
                'offsetSetValue' => $value1,
                'offsetGetOffset' => 0,
                'expectedValue' => $value1,
            ],
        ];
    }

    public function testArrayAccessOffsetExistsOffsetUnset()
    {
        $value = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertFalse($this->container->offsetExists(0));

        $this->container->offsetSet(0, $value);
        $this->assertTrue($this->container->offsetExists(0));

        $this->container->offsetUnset(0);
        $this->assertFalse($this->container->offsetExists(0));
    }

    public function testGetRequests()
    {
        $value0Request = \Mockery::mock(RequestInterface::class);
        $value1Request = \Mockery::mock(RequestInterface::class);

        $value0 = [
            Container::KEY_REQUEST => $value0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $value1 = [
            Container::KEY_REQUEST => $value1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getRequests());

        $this->container[] = $value0;
        $this->container[] = $value1;

        $this->assertEquals(
            [
                $value0Request,
                $value1Request,
            ],
            $this->container->getRequests()
        );
    }

    public function testGetResponses()
    {
        $value0Response = \Mockery::mock(ResponseInterface::class);
        $value1Response = \Mockery::mock(ResponseInterface::class);

        $value0 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => $value0Response,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $value1 = [
            Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            Container::KEY_RESPONSE => $value1Response,
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getResponses());

        $this->container[] = $value0;
        $this->container[] = $value1;

        $this->assertEquals(
            [
                $value0Response,
                $value1Response,
            ],
            $this->container->getResponses()
        );
    }

    public function testGetRequestUrls()
    {
        $value0RequestUri = \Mockery::mock(UriInterface::class);
        $value0RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/0/');

        $value1RequestUri = \Mockery::mock(UriInterface::class);
        $value1RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/1/');

        $value0Request = \Mockery::mock(RequestInterface::class);
        $value0Request
            ->shouldReceive('getUri')
            ->andReturn($value0RequestUri);

        $value1Request = \Mockery::mock(RequestInterface::class);
        $value1Request
            ->shouldReceive('getUri')
            ->andReturn($value1RequestUri);

        $value0 = [
            Container::KEY_REQUEST => $value0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $value1 = [
            Container::KEY_REQUEST => $value1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getRequestUrls());

        $this->container[] = $value0;
        $this->container[] = $value1;

        $this->assertEquals(
            [
                $value0RequestUri,
                $value1RequestUri,
            ],
            $this->container->getRequestUrls()
        );
    }

    public function testGetRequestUrlsAsStrings()
    {
        $value0RequestUri = \Mockery::mock(UriInterface::class);
        $value0RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/0/');

        $value1RequestUri = \Mockery::mock(UriInterface::class);
        $value1RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/1/');

        $value0Request = \Mockery::mock(RequestInterface::class);
        $value0Request
            ->shouldReceive('getUri')
            ->andReturn($value0RequestUri);

        $value1Request = \Mockery::mock(RequestInterface::class);
        $value1Request
            ->shouldReceive('getUri')
            ->andReturn($value1RequestUri);

        $value0 = [
            Container::KEY_REQUEST => $value0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $value1 = [
            Container::KEY_REQUEST => $value1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getRequestUrlsAsStrings());

        $this->container[] = $value0;
        $this->container[] = $value1;

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
        $value0Request = \Mockery::mock(RequestInterface::class);
        $value1Request = \Mockery::mock(RequestInterface::class);

        $value0 = [
            Container::KEY_REQUEST => $value0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $value1 = [
            Container::KEY_REQUEST => $value1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getLastRequest());

        $this->container[] = $value0;
        $this->container[] = $value1;

        $this->assertEquals($value1Request, $this->container->getLastRequest());
    }

    public function testGetLastRequestUrl()
    {
        $value1RequestUri = \Mockery::mock(UriInterface::class);
        $value1RequestUri
            ->shouldReceive('__toString')
            ->andReturn('http://example.com/1/');

        $value0Request = \Mockery::mock(RequestInterface::class);

        $value1Request = \Mockery::mock(RequestInterface::class);
        $value1Request
            ->shouldReceive('getUri')
            ->andReturn($value1RequestUri);

        $value0 = [
            Container::KEY_REQUEST => $value0Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $value1 = [
            Container::KEY_REQUEST => $value1Request,
            Container::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            Container::KEY_ERROR => null,
            Container::KEY_OPTIONS => []
        ];

        $this->assertEmpty($this->container->getLastRequestUrl());

        $this->container[] = $value0;
        $this->container[] = $value1;

        $this->assertEquals($value1RequestUri, $this->container->getLastRequestUrl());
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
    public function invalidValueDataProvider()
    {
        return [
            'not an array' => [
                'value' => null,
                'expectedExceptionMessage' => Container::VALUE_NOT_ARRAY_MESSAGE,
                'expectedExceptionCode' => Container::VALUE_NOT_ARRAY_CODE,
            ],
            'missing request key' => [
                'value' => [],
                'expectedExceptionMessage' => 'Key "request" must be present',
                'expectedExceptionCode' => Container::VALUE_MISSING_KEY_CODE,
            ],
            'missing response key' => [
                'value' => [
                    Container::KEY_REQUEST => null,
                ],
                'expectedExceptionMessage' => 'Key "response" must be present',
                'expectedExceptionCode' => Container::VALUE_MISSING_KEY_CODE,
            ],
            'missing error key' => [
                'value' => [
                    Container::KEY_REQUEST => null,
                    Container::KEY_RESPONSE => null,
                ],
                'expectedExceptionMessage' => 'Key "error" must be present',
                'expectedExceptionCode' => Container::VALUE_MISSING_KEY_CODE,
            ],
            'missing options key' => [
                'value' => [
                    Container::KEY_REQUEST => null,
                    Container::KEY_RESPONSE => null,
                    Container::KEY_ERROR => null,
                ],
                'expectedExceptionMessage' => 'Key "options" must be present',
                'expectedExceptionCode' => Container::VALUE_MISSING_KEY_CODE,
            ],
            'request not a RequestInterface' => [
                'value' => [
                    Container::KEY_REQUEST => null,
                    Container::KEY_RESPONSE => null,
                    Container::KEY_ERROR => null,
                    Container::KEY_OPTIONS => null,
                ],
                'expectedExceptionMessage' => Container::VALUE_REQUEST_NOT_REQUEST_MESSAGE,
                'expectedExceptionCode' => Container::VALUE_REQUEST_NOT_REQUEST_CODE,
            ],
            'response not a ResponseInterface' => [
                'value' => [
                    Container::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                    Container::KEY_RESPONSE => null,
                    Container::KEY_ERROR => null,
                    Container::KEY_OPTIONS => null,
                ],
                'expectedExceptionMessage' => Container::VALUE_RESPONSE_NOT_RESPONSE_MESSAGE,
                'expectedExceptionCode' => Container::VALUE_RESPONSE_NOT_RESPONSE_CODE,
            ],
        ];
    }
}
