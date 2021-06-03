<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use Psr\Http\Message\ResponseInterface;

/**
 * @extends \IteratorAggregate<int, ResponseInterface>
 */
interface ResponseCollectionInterface extends \Countable, \IteratorAggregate
{
    /**
     * @return \Iterator<int, ResponseInterface>
     */
    public function getIterator(): \Iterator;

    public function getLast(): ?ResponseInterface;
}
