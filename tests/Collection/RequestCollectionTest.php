<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\Collection\RequestCollection;
use webignition\HttpHistoryContainer\Collection\UrlCollection;

class RequestCollectionTest extends TestCase
{
    public function testIterable()
    {
        $requests = [
            \Mockery::mock(RequestInterface::class),
            \Mockery::mock(RequestInterface::class),
            \Mockery::mock(RequestInterface::class),
        ];

        $collection = new RequestCollection($requests);
        self::assertInstanceOf(\Traversable::class, $collection);
        self::assertIsIterable($collection);

        foreach ($collection as $requestIndex => $request) {
            self::assertSame($requests[$requestIndex], $request);
        }
    }

    /**
     * @dataProvider countableDataProvider
     */
    public function testCountable(RequestCollection $collection, int $expectedCount)
    {
        self::assertInstanceOf(\Countable::class, $collection);
        self::assertSame($expectedCount, count($collection));
    }

    public function countableDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new RequestCollection([]),
                'expectedCount' => 0,
            ],
            'one' => [
                'collection' => new RequestCollection([
                    \Mockery::mock(RequestInterface::class),
                ]),
                'expectedCount' => 1,
            ],
            'many' => [
                'collection' => new RequestCollection([
                    \Mockery::mock(RequestInterface::class),
                    \Mockery::mock(RequestInterface::class),
                    \Mockery::mock(RequestInterface::class),
                ]),
                'expectedCount' => 3,
            ],
        ];
    }

    /**
     * @dataProvider getLastDataProvider
     */
    public function testGetLast(RequestCollection $collection, ?RequestInterface $expectedLast)
    {
        self::assertSame($expectedLast, $collection->getLast());
    }

    public function getLastDataProvider(): array
    {
        $firstRequest = \Mockery::mock(RequestInterface::class);
        $lastRequest = \Mockery::mock(RequestInterface::class);

        return [
            'empty' => [
                'collection' => new RequestCollection([]),
                'expectedLast' => null,
            ],
            'one' => [
                'collection' => new RequestCollection([
                    $firstRequest,
                ]),
                'expectedLast' => $firstRequest,
            ],
            'many' => [
                'collection' => new RequestCollection([
                    $firstRequest,
                    \Mockery::mock(RequestInterface::class),
                    $lastRequest,
                ]),
                'expectedLast' => $lastRequest,
            ],
        ];
    }

    /**
     * @dataProvider getUrlsDataProvider
     */
    public function testGetUrls(RequestCollection $collection, UrlCollection $expectedUrls)
    {
        self::assertEquals($expectedUrls, $collection->getUrls());
    }

    public function getUrlsDataProvider(): array
    {
        $firstUri = \Mockery::mock(UriInterface::class);
        $firstRequest = \Mockery::mock(RequestInterface::class);
        $firstRequest
            ->shouldReceive('getUri')
            ->andReturn($firstUri);

        $secondUri = \Mockery::mock(UriInterface::class);
        $secondRequest = \Mockery::mock(RequestInterface::class);
        $secondRequest
            ->shouldReceive('getUri')
            ->andReturn($secondUri);

        $thirdUri = \Mockery::mock(UriInterface::class);
        $thirdRequest = \Mockery::mock(RequestInterface::class);
        $thirdRequest
            ->shouldReceive('getUri')
            ->andReturn($thirdUri);

        return [
            'empty' => [
                'collection' => new RequestCollection([]),
                'expectedUrls' => new UrlCollection([]),
            ],
            'one' => [
                'collection' => new RequestCollection([
                    $firstRequest,
                ]),
                'expectedUrls' => new UrlCollection([
                    $firstUri,
                ]),
            ],
            'many' => [
                'collection' => new RequestCollection([
                    $firstRequest,
                    $secondRequest,
                    $thirdRequest,
                ]),
                'expectedUrls' => new UrlCollection([
                    $firstUri,
                    $secondUri,
                    $thirdUri,
                ]),
            ],
        ];
    }
}
