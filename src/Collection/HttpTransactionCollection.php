<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

/**
 * @implements \IteratorAggregate<int, HttpTransaction>
 */
class HttpTransactionCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var HttpTransaction[]
     */
    private array $transactions = [];

    public function add(HttpTransaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    public function get(int $offset): ?HttpTransaction
    {
        return $this->transactions[$offset] ?? null;
    }

    public function remove(int $offset): void
    {
        unset($this->transactions[$offset]);
    }

    /**
     * @return HttpTransaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function count(): int
    {
        return count($this->transactions);
    }

    /**
     * @return \Iterator<HttpTransaction>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->transactions);
    }

    public function getRequests(): RequestCollection
    {
        $requests = [];
        foreach ($this->transactions as $transaction) {
            $requests[] = $transaction->getRequest();
        }

        return new RequestCollection($requests);
    }

    public function getResponses(): ResponseCollection
    {
        $responses = [];
        foreach ($this->transactions as $transaction) {
            $responses[] = $transaction->getResponse();
        }

        return new ResponseCollection($responses);
    }

    public function clear(): void
    {
        $this->transactions = [];
    }
}
