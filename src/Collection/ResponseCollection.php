<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use Psr\Http\Message\ResponseInterface;

/**
 * @implements \IteratorAggregate<int, ResponseInterface>
 */
class ResponseCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var ResponseInterface[]
     */
    private array $responses = [];

    /**
     * @param array<mixed> $responses
     */
    public function __construct(array $responses)
    {
        foreach ($responses as $response) {
            if ($response instanceof ResponseInterface) {
                $this->responses[] = $response;
            }
        }
    }

    /**
     * @return \Iterator<int, ResponseInterface>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->responses);
    }

    public function count(): int
    {
        return count($this->responses);
    }

    public function getLast(): ?ResponseInterface
    {
        $requests = $this->responses;

        return array_pop($requests);
    }
}
