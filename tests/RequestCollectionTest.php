<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\RequestCollection;

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
     * @dataProvider getLastUrlDataProvider
     */
    public function testGetLastUrl(RequestCollection $collection, ?UriInterface $expectedUrl)
    {
        self::assertSame($expectedUrl, $collection->getLastUrl());
    }

    public function getLastUrlDataProvider(): array
    {
        $firstUri = \Mockery::mock(UriInterface::class);

        $firstRequest = \Mockery::mock(RequestInterface::class);
        $firstRequest
            ->shouldReceive('getUri')
            ->andReturn($firstUri);

        $lastUri = \Mockery::mock(UriInterface::class);

        $lastRequest = \Mockery::mock(RequestInterface::class);
        $lastRequest
            ->shouldReceive('getUri')
            ->andReturn($lastUri);

        return [
            'empty' => [
                'collection' => new RequestCollection([]),
                'expectedUrl' => null,
            ],
            'one' => [
                'collection' => new RequestCollection([
                    $firstRequest,
                ]),
                'expectedUrl' => $firstUri,
            ],
            'many' => [
                'collection' => new RequestCollection([
                    $firstRequest,
                    \Mockery::mock(RequestInterface::class),
                    $lastRequest,
                ]),
                'expectedUrl' => $lastUri,
            ],
        ];
    }
}
