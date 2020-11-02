<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\UrlCollection;

class UrlCollectionTest extends TestCase
{
    public function testIterable()
    {
        $urls = [
            \Mockery::mock(UriInterface::class),
            \Mockery::mock(UriInterface::class),
            \Mockery::mock(UriInterface::class),
        ];

        $collection = new UrlCollection($urls);
        self::assertInstanceOf(\Traversable::class, $collection);
        self::assertIsIterable($collection);

        foreach ($collection as $urlIndex => $url) {
            self::assertSame($urls[$urlIndex], $url);
        }
    }

    /**
     * @dataProvider countableDataProvider
     */
    public function testCountable(UrlCollection $collection, int $expectedCount)
    {
        self::assertInstanceOf(\Countable::class, $collection);
        self::assertSame($expectedCount, count($collection));
    }

    public function countableDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new UrlCollection([]),
                'expectedCount' => 0,
            ],
            'one' => [
                'collection' => new UrlCollection([
                    \Mockery::mock(UriInterface::class),
                ]),
                'expectedCount' => 1,
            ],
            'many' => [
                'collection' => new UrlCollection([
                    \Mockery::mock(UriInterface::class),
                    \Mockery::mock(UriInterface::class),
                    \Mockery::mock(UriInterface::class),
                ]),
                'expectedCount' => 3,
            ],
        ];
    }

    /**
     * @dataProvider getLastDataProvider
     */
    public function testGetLast(UrlCollection $collection, ?UriInterface $expectedLast)
    {
        self::assertSame($expectedLast, $collection->getLast());
    }

    public function getLastDataProvider(): array
    {
        $firstUrl = \Mockery::mock(UriInterface::class);
        $lastUrl = \Mockery::mock(UriInterface::class);

        return [
            'empty' => [
                'collection' => new UrlCollection([]),
                'expectedLast' => null,
            ],
            'one' => [
                'collection' => new UrlCollection([
                    $firstUrl,
                ]),
                'expectedLast' => $firstUrl,
            ],
            'many' => [
                'collection' => new UrlCollection([
                    $firstUrl,
                    \Mockery::mock(UriInterface::class),
                    $lastUrl,
                ]),
                'expectedLast' => $lastUrl,
            ],
        ];
    }
}
