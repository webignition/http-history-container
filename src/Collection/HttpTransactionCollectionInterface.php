<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use webignition\HttpHistoryContainer\Transaction\HttpTransactionInterface;

/**
 * @extends \IteratorAggregate<int, HttpTransactionInterface>
 */
interface HttpTransactionCollectionInterface extends \Countable, \IteratorAggregate
{
    public function getPeriods(): PeriodCollectionInterface;
    public function add(HttpTransactionInterface $transaction): void;
    public function get(int $offset): ?HttpTransactionInterface;

    /**
     * @return HttpTransactionInterface[]
     */
    public function getTransactions(): array;

    /**
     * @return \Iterator<HttpTransactionInterface>
     */
    public function getIterator(): \Iterator;

    public function getRequests(): RequestCollectionInterface;
    public function getResponses(): ResponseCollectionInterface;

    public function clear(): void;

    public function slice(int $offset, ?int $length): HttpTransactionCollectionInterface;
}
