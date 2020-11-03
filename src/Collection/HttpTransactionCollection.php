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

    /**
     * @param HttpTransaction $transaction
     * @param int $offset
     *
     * @throws InvalidTransactionOffsetException
     */
    public function addAtOffset(HttpTransaction $transaction, int $offset): void
    {
        if ($offset < 0) {
            throw new InvalidTransactionOffsetException($transaction, $offset);
        }

        $this->transactions[$offset] = $transaction;
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
