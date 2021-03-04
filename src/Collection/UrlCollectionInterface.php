<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use Psr\Http\Message\UriInterface;

/**
 * @extends \IteratorAggregate<int, UriInterface>
 */
interface UrlCollectionInterface extends \Countable, \IteratorAggregate
{
    /**
     * @return \Iterator<int, UriInterface>
     */
    public function getIterator(): \Iterator;
    public function getLast(): ?UriInterface;

    /**
     * @return string[]
     */
    public function getAsStrings(): array;
}
