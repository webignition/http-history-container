<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @implements \IteratorAggregate<int, RequestInterface>
 */
class RequestCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var RequestInterface[]
     */
    private array $requests = [];

    /**
     * @param array<mixed> $requests
     */
    public function __construct(array $requests)
    {
        foreach ($requests as $request) {
            if ($request instanceof RequestInterface) {
                $this->requests[] = $request;
            }
        }
    }

    /**
     * @return \Iterator<int, RequestInterface>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->requests);
    }

    public function count(): int
    {
        return count($this->requests);
    }

    public function getLast(): ?RequestInterface
    {
        $requests = $this->requests;

        return array_pop($requests);
    }

    public function getLastUrl(): ?UriInterface
    {
        $lastRequest = $this->getLast();

        return $lastRequest instanceof RequestInterface ? $lastRequest->getUri() : null;
    }
}
