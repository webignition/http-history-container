<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;
use webignition\HttpHistoryContainer\Collection\HttpTransactionCollectionInterface;
use webignition\HttpHistoryContainer\Collection\PeriodCollection;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\HttpHistoryContainer\Transaction\HttpTransactionInterface;
use webignition\HttpHistoryContainer\Transaction\LoggableTransaction;

class HttpTransactionCollectionTest extends TestCase
{
    private HttpTransactionCollection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new HttpTransactionCollection();
    }

    public function testAdd(): void
    {
        self::assertCount(0, $this->collection);

        $transactions = [
            \Mockery::mock(HttpTransactionInterface::class),
            \Mockery::mock(HttpTransactionInterface::class),
            \Mockery::mock(HttpTransactionInterface::class),
        ];

        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        self::assertSame($transactions, $this->collection->getTransactions());
    }

    public function testGet(): void
    {
        self::assertCount(0, $this->collection);

        $transactions = [
            \Mockery::mock(HttpTransactionInterface::class),
            \Mockery::mock(HttpTransactionInterface::class),
            \Mockery::mock(HttpTransactionInterface::class),
        ];

        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        self::assertNull($this->collection->get(-1));
        self::assertNull($this->collection->get(count($transactions)));

        foreach ($transactions as $transactionIndex => $transaction) {
            self::assertSame($transaction, $this->collection->get($transactionIndex));
        }
    }

    public function testIterator(): void
    {
        $transactions = [
            \Mockery::mock(HttpTransactionInterface::class),
            \Mockery::mock(HttpTransactionInterface::class),
            \Mockery::mock(HttpTransactionInterface::class),
        ];

        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        foreach ($this->collection as $transactionIndex => $transaction) {
            self::assertSame($transactions[$transactionIndex], $transaction);
        }
    }

    public function testGetRequests(): void
    {
        $requests = [
            \Mockery::mock(RequestInterface::class),
            \Mockery::mock(RequestInterface::class),
            \Mockery::mock(RequestInterface::class),
        ];

        foreach ($requests as $request) {
            $this->collection->add(
                new HttpTransaction(
                    $request,
                    \Mockery::mock(ResponseInterface::class),
                    null,
                    []
                )
            );
        }

        $requestCollection = $this->collection->getRequests();

        foreach ($requestCollection as $index => $request) {
            self::assertSame($requests[$index], $request);
        }
    }

    public function testGetResponses(): void
    {
        $responses = [
            \Mockery::mock(ResponseInterface::class),
            \Mockery::mock(ResponseInterface::class),
            \Mockery::mock(ResponseInterface::class),
        ];

        foreach ($responses as $response) {
            $this->collection->add(
                new HttpTransaction(
                    \Mockery::mock(RequestInterface::class),
                    $response,
                    null,
                    []
                )
            );
        }

        $responseCollection = $this->collection->getResponses();

        foreach ($responseCollection as $index => $response) {
            self::assertSame($responses[$index], $response);
        }
    }

    public function testClear(): void
    {
        self::assertCount(0, $this->collection);

        $transactions = [
            \Mockery::mock(HttpTransactionInterface::class),
            \Mockery::mock(HttpTransactionInterface::class),
            \Mockery::mock(HttpTransactionInterface::class),
        ];

        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        self::assertCount(count($transactions), $this->collection);

        $this->collection->clear();
        self::assertCount(0, $this->collection);
    }

    /**
     * @param HttpTransactionInterface[] $transactions
     */
    #[DataProvider('getPeriodsDataProvider')]
    public function testGetPeriods(array $transactions, callable $assertions): void
    {
        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        $assertions($this->collection->getPeriods());
    }

    /**
     * @return array<mixed>
     */
    public static function getPeriodsDataProvider(): array
    {
        return [
            'non-timed transactions' => [
                'transactions' => [
                    \Mockery::mock(HttpTransactionInterface::class),
                    \Mockery::mock(HttpTransactionInterface::class),
                    \Mockery::mock(HttpTransactionInterface::class),
                ],
                'assertions' => function (PeriodCollection $periodCollection) {
                    self::assertCount(3, $periodCollection);

                    $currentPeriod = 0;
                    foreach ($periodCollection as $period) {
                        self::assertLessThanOrEqual(10, $period - $currentPeriod);
                        $currentPeriod = $period;
                    }
                },
            ],
            'timed transactions' => [
                'transactions' => [
                    self::createLoggableTransactionWithPeriod(0),
                    self::createLoggableTransactionWithPeriod(10),
                    self::createLoggableTransactionWithPeriod(200),
                ],
                'assertions' => function (PeriodCollection $periodCollection) {
                    self::assertSame([0, 10, 200], $periodCollection->getPeriodsInMicroseconds());
                },
            ],
        ];
    }

    #[DataProvider('sliceDataProvider')]
    public function testSlice(
        HttpTransactionCollectionInterface $collection,
        int $offset,
        ?int $length,
        HttpTransactionCollectionInterface $expectedCollection
    ): void {
        $mutatedCollection = $collection->slice($offset, $length);

        self::assertEquals($expectedCollection->getTransactions(), $mutatedCollection->getTransactions());
    }

    /**
     * @return array<mixed>
     */
    public static function sliceDataProvider(): array
    {
        $transaction1 = new HttpTransaction(
            \Mockery::mock(RequestInterface::class),
            null,
            null,
            [
                'item-one-option-key' => 'item-one-option-value',
            ]
        );

        $transaction2 = new HttpTransaction(
            \Mockery::mock(RequestInterface::class),
            null,
            null,
            [
                'item-two-option-key' => 'item-two-option-value',
            ]
        );

        $transaction3 = new HttpTransaction(
            \Mockery::mock(RequestInterface::class),
            null,
            null,
            [
                'item-three-option-key' => 'item-three-option-value',
            ]
        );

        return [
            'empty' => [
                'collection' => new HttpTransactionCollection(),
                'offset' => 0,
                'length' => null,
                'expectedCollection' => new HttpTransactionCollection(),
            ],
            'offset: 0, length: null, single item collection' => [
                'collection' => self::createCollection([$transaction1]),
                'offset' => 0,
                'length' => null,
                'expectedCollection' => self::createCollection([$transaction1]),
            ],
            'offset: 0, length: null, triple item collection' => [
                'collection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
                'offset' => 0,
                'length' => null,
                'expectedCollection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
            ],
            'offset: 0, length: 1, triple item collection' => [
                'collection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
                'offset' => 0,
                'length' => 1,
                'expectedCollection' => self::createCollection([$transaction1]),
            ],
            'offset: 0, length: 2, triple item collection' => [
                'collection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
                'offset' => 0,
                'length' => 2,
                'expectedCollection' => self::createCollection([$transaction1, $transaction2]),
            ],
            'offset: 0, length: 3, triple item collection' => [
                'collection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
                'offset' => 0,
                'length' => 3,
                'expectedCollection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
            ],
            'offset: 0, length: 4, triple item collection' => [
                'collection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
                'offset' => 0,
                'length' => 4,
                'expectedCollection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
            ],
            'offset: -1, length: null, triple item collection' => [
                'collection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
                'offset' => -1,
                'length' => null,
                'expectedCollection' => self::createCollection([$transaction3]),
            ],
            'offset: -2, length: null, triple item collection' => [
                'collection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
                'offset' => -2,
                'length' => null,
                'expectedCollection' => self::createCollection([$transaction2, $transaction3]),
            ],
            'offset: -3, length: null, triple item collection' => [
                'collection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
                'offset' => -3,
                'length' => null,
                'expectedCollection' => self::createCollection([$transaction1, $transaction2, $transaction3]),
            ],
        ];
    }

    private static function createLoggableTransactionWithPeriod(int $period): LoggableTransaction
    {
        $transaction = \Mockery::mock(LoggableTransaction::class);
        $transaction
            ->shouldReceive('getPeriod')
            ->andReturn($period)
        ;

        return $transaction;
    }

    /**
     * @param HttpTransactionInterface[] $transactions
     */
    private static function createCollection(array $transactions): HttpTransactionCollectionInterface
    {
        $collection = new HttpTransactionCollection();

        foreach ($transactions as $transaction) {
            $collection->add($transaction);
        }

        return $collection;
    }
}
