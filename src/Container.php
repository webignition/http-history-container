<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

/**
 * @implements \ArrayAccess<int, mixed>
 * @implements \IteratorAggregate<HttpTransaction>
 */
class Container implements \ArrayAccess, \IteratorAggregate, \Countable
{
    public const OFFSET_INVALID_MESSAGE = 'Invalid offset; must always be null';
    public const OFFSET_INVALID_CODE = 1;

    private HttpTransactionCollection $transactions;

    public function __construct()
    {
        $this->transactions = new HttpTransactionCollection();
    }

    public function getTransactions(): HttpTransactionCollection
    {
        return $this->transactions;
    }

    /**
     * @param mixed $offset
     * @param mixed $httpTransactionData
     *
     * @throws InvalidTransactionException
     */
    public function offsetSet($offset, $httpTransactionData): void
    {
        if (null !== $offset) {
            throw new \InvalidArgumentException(
                self::OFFSET_INVALID_MESSAGE,
                self::OFFSET_INVALID_CODE
            );
        }

        $httpTransaction = HttpTransaction::fromArray($httpTransactionData);

        $this->transactions->add($httpTransaction);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (false === is_int($offset)) {
            return false;
        }

        return $this->offsetGet($offset) instanceof HttpTransaction;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        if (is_int($offset)) {
            $this->transactions->remove($offset);
        }
    }

    /**
     * @param mixed $offset
     *
     * @return HttpTransaction|null
     */
    public function offsetGet($offset): ?HttpTransaction
    {
        return null === $offset
            ? null
            : $this->transactions->get($offset);
    }

    /**
     * @return \Iterator<HttpTransaction>
     */
    public function getIterator(): \Iterator
    {
        return $this->transactions->getIterator();
    }

    public function count(): int
    {
        return count($this->transactions);
    }

    public function clear(): void
    {
        $this->transactions->clear();
    }
}
