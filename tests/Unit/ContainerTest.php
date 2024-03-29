<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;
use webignition\HttpHistoryContainer\Collection\PeriodCollection;
use webignition\HttpHistoryContainer\Container;
use webignition\HttpHistoryContainer\InvalidTransactionException;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\ObjectReflector\ObjectReflector;

class ContainerTest extends TestCase
{
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

        $container = new Container();
        $container->offsetSet($offset, null);
    }

    /**
     * @return array<mixed>
     */
    public static function invalidOffsetDataProvider(): array
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

    public function testOffsetSetInvalidHttpTransaction(): void
    {
        $data = [];

        $this->expectExceptionObject(InvalidTransactionException::createForInvalidRequest($data));

        $container = new Container();
        $container->offsetSet(null, $data);
    }

    /**
     * @dataProvider offsetDataProvider
     *
     * @param array<mixed> $transactionData
     */
    public function testOffsetSet(
        Container $container,
        array $transactionData,
        HttpTransactionCollection $expectedTransactions
    ): void {
        $container->offsetSet(null, $transactionData);
        $transactions = $container->getTransactions();

        $this->setTransactionPeriodCollectionToEmpty($transactions);
        $this->setTransactionPeriodCollectionToEmpty($expectedTransactions);

        self::assertEquals($expectedTransactions, $transactions);
    }

    /**
     * @return array<mixed>
     */
    public static function offsetDataProvider(): array
    {
        $httpTransaction0Data = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            HttpTransaction::KEY_ERROR => null,
            HttpTransaction::KEY_OPTIONS => [
                'value_0_options_key' => 'value_0_options_value',
            ]
        ];

        $httpTransaction1Data = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            HttpTransaction::KEY_ERROR => null,
            HttpTransaction::KEY_OPTIONS => [
                'value_1_options_key' => 'value_1_options_value',
            ]
        ];

        return [
            'no existing transactions' => [
                'container' => new Container(),
                'transactionData' => $httpTransaction0Data,
                'expectedTransactions' => self::createHttpTransactionCollection([
                    HttpTransaction::fromArray($httpTransaction0Data),
                ]),
            ],
            'has existing transaction' => [
                'container' => self::createContainer([
                    $httpTransaction0Data
                ]),
                'transactionData' => $httpTransaction1Data,
                'expectedTransactions' => self::createHttpTransactionCollection([
                    HttpTransaction::fromArray($httpTransaction0Data),
                    HttpTransaction::fromArray($httpTransaction1Data),
                ]),
            ],
        ];
    }

    public function testOffsetGetOffsetNull(): void
    {
        $container = self::createContainer([
            [
                HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
                HttpTransaction::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            ],
        ]);

        self::assertCount(1, $container);
        self::assertNull($container->offsetGet(null));
    }

    /**
     * @dataProvider arrayAccessOffsetSetOffsetGetDataProvider
     */
    public function testOffsetGet(
        Container $container,
        int $offset,
        ?HttpTransaction $expectedHttpTransaction
    ): void {
        self::assertEquals($expectedHttpTransaction, $container->offsetGet($offset));
    }

    /**
     * @return array<mixed>
     */
    public static function arrayAccessOffsetSetOffsetGetDataProvider(): array
    {
        $httpTransaction0Data = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            HttpTransaction::KEY_ERROR => null,
            HttpTransaction::KEY_OPTIONS => [
                'value_0_options_key' => 'value_0_options_value',
            ]
        ];

        $httpTransaction1Data = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            HttpTransaction::KEY_ERROR => null,
            HttpTransaction::KEY_OPTIONS => [
                'value_1_options_key' => 'value_1_options_value',
            ]
        ];

        return [
            'no existing transactions; offset=0' => [
                'container' => new Container(),
                'offset' => 0,
                'expectedHttpTransaction' => null,
            ],
            'no existing transactions; offset=1' => [
                'container' => new Container(),
                'offset' => 1,
                'expectedHttpTransaction' => null,
            ],
            'has existing transactions; offset=0' => [
                'container' => self::createContainer([
                    $httpTransaction0Data,
                    $httpTransaction1Data,
                ]),
                'offset' => 0,
                'expectedHttpTransaction' => HttpTransaction::fromArray($httpTransaction0Data),
            ],
            'has existing transactions; offset=1' => [
                'container' => self::createContainer([
                    $httpTransaction0Data,
                    $httpTransaction1Data,
                ]),
                'offset' => 1,
                'expectedHttpTransaction' => HttpTransaction::fromArray($httpTransaction1Data),
            ],
        ];
    }

    public function testOffsetExists(): void
    {
        $httpTransaction0Data = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            HttpTransaction::KEY_ERROR => null,
            HttpTransaction::KEY_OPTIONS => [
                'value_0_options_key' => 'value_0_options_value',
            ]
        ];

        $httpTransaction1Data = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
            HttpTransaction::KEY_ERROR => null,
            HttpTransaction::KEY_OPTIONS => [
                'value_1_options_key' => 'value_1_options_value',
            ]
        ];

        $container = new Container();
        self::assertfalse($container->offsetExists(null));
        self::assertfalse($container->offsetExists('string'));
        self::assertfalse($container->offsetExists(true));
        self::assertfalse($container->offsetExists(0));
        self::assertfalse($container->offsetExists(1));

        $container->offsetSet(null, $httpTransaction0Data);
        $container->offsetSet(null, $httpTransaction1Data);
        self::assertfalse($container->offsetExists(null));
        self::assertfalse($container->offsetExists('string'));
        self::assertfalse($container->offsetExists(true));
        self::assertTrue($container->offsetExists(0));
        self::assertTrue($container->offsetExists(1));
    }

    public function testIterator(): void
    {
        $httpTransaction0Response = \Mockery::mock(ResponseInterface::class);
        $httpTransaction1Response = \Mockery::mock(ResponseInterface::class);

        $httpTransaction0Data = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => $httpTransaction0Response,
        ];

        $httpTransaction1Data = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => $httpTransaction1Response,
        ];

        $httpTransactions = [
            HttpTransaction::fromArray($httpTransaction0Data),
            HttpTransaction::fromArray($httpTransaction1Data),
        ];

        $container = new Container();

        $container[] = $httpTransaction0Data;
        $container[] = $httpTransaction1Data;

        $iteratedTransactionCount = 0;

        foreach ($container as $httpTransactionIndex => $httpTransaction) {
            ++$iteratedTransactionCount;
            self::assertEquals($httpTransactions[$httpTransactionIndex], $httpTransaction);
        }

        self::assertEquals(2, $iteratedTransactionCount);
    }

    public function testClear(): void
    {
        $httpTransaction = [
            HttpTransaction::KEY_REQUEST => \Mockery::mock(RequestInterface::class),
            HttpTransaction::KEY_RESPONSE => \Mockery::mock(ResponseInterface::class),
        ];

        $container = new Container();

        $container[] = $httpTransaction;
        self::assertCount(1, $container);

        $container->clear();
        self::assertCount(0, $container);
    }

    /**
     * @param HttpTransaction[] $transactions
     */
    private static function createHttpTransactionCollection(array $transactions): HttpTransactionCollection
    {
        $collection = new HttpTransactionCollection();

        foreach ($transactions as $transaction) {
            $collection->add($transaction);
        }

        return $collection;
    }

    /**
     * @param array<mixed> $transactionDataSets
     */
    private static function createContainer(array $transactionDataSets): Container
    {
        $container = new Container();

        foreach ($transactionDataSets as $transactionData) {
            $container->offsetSet(null, $transactionData);
        }

        return $container;
    }

    private function setTransactionPeriodCollectionToEmpty(HttpTransactionCollection $collection): void
    {
        ObjectReflector::setProperty(
            $collection,
            HttpTransactionCollection::class,
            'periods',
            new PeriodCollection()
        );
    }
}
