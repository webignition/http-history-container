<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Collection;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

class HttpTransactionCollectionTest extends TestCase
{
    private HttpTransactionCollection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new HttpTransactionCollection();
    }

    public function testAdd()
    {
        self::assertCount(0, $this->collection);

        $transactions = [
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
        ];

        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        self::assertSame($transactions, $this->collection->getTransactions());
    }

    public function testGet()
    {
        self::assertCount(0, $this->collection);

        $transactions = [
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
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

    public function testRemove()
    {
        self::assertCount(0, $this->collection);

        $transactions = [
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
        ];

        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        self::assertSame($transactions, $this->collection->getTransactions());

        $this->collection->remove(2);
        self::assertSame(array_slice($transactions, 0, 2), $this->collection->getTransactions());

        $this->collection->remove(1);
        self::assertSame(array_slice($transactions, 0, 1), $this->collection->getTransactions());

        $this->collection->remove(0);
        self::assertSame([], $this->collection->getTransactions());
    }

    public function testIterator()
    {
        $transactions = [
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
        ];

        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        foreach ($this->collection as $transactionIndex => $transaction) {
            self::assertSame($transactions[$transactionIndex], $transaction);
        }
    }

    public function testGetRequests()
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

    public function testGetResponses()
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

    public function testClear()
    {
        self::assertCount(0, $this->collection);

        $transactions = [
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
            \Mockery::mock(HttpTransaction::class),
        ];

        foreach ($transactions as $transaction) {
            $this->collection->add($transaction);
        }

        self::assertCount(count($transactions), $this->collection);

        $this->collection->clear();
        self::assertCount(0, $this->collection);
    }
}
