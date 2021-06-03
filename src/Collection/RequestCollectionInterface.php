<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use Psr\Http\Message\RequestInterface;

/**
 * @extends \IteratorAggregate<int, RequestInterface>
 */
interface RequestCollectionInterface extends \Countable, \IteratorAggregate
{
    /**
     * @return \Iterator<int, RequestInterface>
     */
    public function getIterator(): \Iterator;

    public function getLast(): ?RequestInterface;

    public function getUrls(): UrlCollectionInterface;
}
