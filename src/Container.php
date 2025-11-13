<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;
use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\HttpHistoryContainer\Transaction\HttpTransactionInterface;

/**
 * @implements \ArrayAccess<int, array>
 * @implements \IteratorAggregate<HttpTransactionInterface>
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
     * @param null|mixed $offset
     *
     * @throws InvalidTransactionException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null !== $offset) {
            throw new \InvalidArgumentException(
                self::OFFSET_INVALID_MESSAGE,
                self::OFFSET_INVALID_CODE
            );
        }

        $httpTransaction = HttpTransaction::fromArray($value);

        $this->transactions->add($httpTransaction);
    }

    /**
     * @param int|mixed $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        if (false === is_int($offset)) {
            return false;
        }

        return $this->offsetGet($offset) instanceof HttpTransactionInterface;
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    /**
     * @param ?mixed $offset
     */
    public function offsetGet(mixed $offset): ?HttpTransactionInterface
    {
        $offset = is_int($offset) ? $offset : null;

        return null === $offset
            ? null
            : $this->transactions->get($offset);
    }

    /**
     * @return \Traversable<int, HttpTransactionInterface>
     */
    public function getIterator(): \Traversable
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
