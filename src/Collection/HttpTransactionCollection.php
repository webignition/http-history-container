<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use webignition\HttpHistoryContainer\Transaction\HttpTransactionInterface;
use webignition\HttpHistoryContainer\Transaction\WithPeriodInterface;

class HttpTransactionCollection implements HttpTransactionCollectionInterface
{
    /**
     * @var HttpTransactionInterface[]
     */
    private array $transactions = [];

    private PeriodCollectionInterface $periods;

    public function __construct()
    {
        $this->periods = new PeriodCollection();
    }

    public function getPeriods(): PeriodCollectionInterface
    {
        return $this->periods;
    }

    public function add(HttpTransactionInterface $transaction): void
    {
        $this->transactions[] = $transaction;

        if ($transaction instanceof WithPeriodInterface) {
            $this->periods->append($transaction->getPeriod());
        } else {
            $this->periods->add();
        }
    }

    public function get(int $offset): ?HttpTransactionInterface
    {
        return $this->transactions[$offset] ?? null;
    }

    /**
     * @return HttpTransactionInterface[]
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
     * @return \Iterator<HttpTransactionInterface>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->transactions);
    }

    public function getRequests(): RequestCollectionInterface
    {
        $requests = [];
        foreach ($this->transactions as $transaction) {
            $requests[] = $transaction->getRequest();
        }

        return new RequestCollection($requests);
    }

    public function getResponses(): ResponseCollectionInterface
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

    public function slice(int $offset, ?int $length): HttpTransactionCollectionInterface
    {
        $slicedTransactions = array_slice($this->transactions, $offset, $length);

        $collection = new HttpTransactionCollection();
        foreach ($slicedTransactions as $transaction) {
            $collection->add($transaction);
        }

        return $collection;
    }
}
