<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\Collection\RequestCollection;
use webignition\HttpHistoryContainer\Collection\UrlCollection;

class RequestCollectionTest extends TestCase
{
    public function testIterable(): void
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

    #[DataProvider('countableDataProvider')]
    public function testCountable(RequestCollection $collection, int $expectedCount): void
    {
        self::assertInstanceOf(\Countable::class, $collection);
        self::assertCount($expectedCount, $collection);
    }

    /**
     * @return array<mixed>
     */
    public static function countableDataProvider(): array
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

    #[DataProvider('getLastDataProvider')]
    public function testGetLast(RequestCollection $collection, ?RequestInterface $expectedLast): void
    {
        self::assertSame($expectedLast, $collection->getLast());
    }

    /**
     * @return array<mixed>
     */
    public static function getLastDataProvider(): array
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

    #[DataProvider('getUrlsDataProvider')]
    public function testGetUrls(RequestCollection $collection, UrlCollection $expectedUrls): void
    {
        self::assertEquals($expectedUrls, $collection->getUrls());
    }

    /**
     * @return array<mixed>
     */
    public static function getUrlsDataProvider(): array
    {
        $firstUri = \Mockery::mock(UriInterface::class);
        $firstRequest = \Mockery::mock(RequestInterface::class);
        $firstRequest
            ->shouldReceive('getUri')
            ->andReturn($firstUri)
        ;

        $secondUri = \Mockery::mock(UriInterface::class);
        $secondRequest = \Mockery::mock(RequestInterface::class);
        $secondRequest
            ->shouldReceive('getUri')
            ->andReturn($secondUri)
        ;

        $thirdUri = \Mockery::mock(UriInterface::class);
        $thirdRequest = \Mockery::mock(RequestInterface::class);
        $thirdRequest
            ->shouldReceive('getUri')
            ->andReturn($thirdUri)
        ;

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
