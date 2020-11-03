<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Collection\ResponseCollection;

class ResponseCollectionTest extends TestCase
{
    public function testIterable()
    {
        $responses = [
            \Mockery::mock(ResponseInterface::class),
            \Mockery::mock(ResponseInterface::class),
            \Mockery::mock(ResponseInterface::class),
        ];

        $collection = new ResponseCollection($responses);
        self::assertInstanceOf(\Traversable::class, $collection);
        self::assertIsIterable($collection);

        foreach ($collection as $responseIndex => $response) {
            self::assertSame($responses[$responseIndex], $response);
        }
    }

    /**
     * @dataProvider countableDataProvider
     */
    public function testCountable(ResponseCollection $collection, int $expectedCount)
    {
        self::assertInstanceOf(\Countable::class, $collection);
        self::assertSame($expectedCount, count($collection));
    }

    public function countableDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new ResponseCollection([]),
                'expectedCount' => 0,
            ],
            'one' => [
                'collection' => new ResponseCollection([
                    \Mockery::mock(ResponseInterface::class),
                ]),
                'expectedCount' => 1,
            ],
            'many' => [
                'collection' => new ResponseCollection([
                    \Mockery::mock(ResponseInterface::class),
                    \Mockery::mock(ResponseInterface::class),
                    \Mockery::mock(ResponseInterface::class),
                ]),
                'expectedCount' => 3,
            ],
        ];
    }

    /**
     * @dataProvider getLastDataProvider
     */
    public function testGetLast(ResponseCollection $collection, ?ResponseInterface $expectedLast)
    {
        self::assertSame($expectedLast, $collection->getLast());
    }

    public function getLastDataProvider(): array
    {
        $firstRequest = \Mockery::mock(ResponseInterface::class);
        $lastRequest = \Mockery::mock(ResponseInterface::class);

        return [
            'empty' => [
                'collection' => new ResponseCollection([]),
                'expectedLast' => null,
            ],
            'one' => [
                'collection' => new ResponseCollection([
                    $firstRequest,
                ]),
                'expectedLast' => $firstRequest,
            ],
            'many' => [
                'collection' => new ResponseCollection([
                    $firstRequest,
                    \Mockery::mock(ResponseInterface::class),
                    $lastRequest,
                ]),
                'expectedLast' => $lastRequest,
            ],
        ];
    }
}