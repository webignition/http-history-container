<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\UriInterface;

/**
 * @implements \IteratorAggregate<int, UriInterface>
 */
class UrlCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var UriInterface[]
     */
    private array $urls = [];

    /**
     * @param array<mixed> $urls
     */
    public function __construct(array $urls)
    {
        foreach ($urls as $url) {
            if ($url instanceof UriInterface) {
                $this->urls[] = $url;
            }
        }
    }

    /**
     * @return \Iterator<int, UriInterface>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->urls);
    }

    public function count(): int
    {
        return count($this->urls);
    }

    public function getLast(): ?UriInterface
    {
        $urls = $this->urls;

        return array_pop($urls);
    }
}
