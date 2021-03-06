<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use Psr\Http\Message\UriInterface;

class UrlCollection implements UrlCollectionInterface
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

    /**
     * @return string[]
     */
    public function getAsStrings(): array
    {
        $urlStrings = [];

        foreach ($this as $url) {
            $urlStrings[] = (string) $url;
        }

        return $urlStrings;
    }
}
