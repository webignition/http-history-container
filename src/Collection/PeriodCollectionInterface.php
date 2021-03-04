<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

/**
 * @extends \IteratorAggregate<int, int>
 */
interface PeriodCollectionInterface extends \Countable, \IteratorAggregate
{
    public function add(): void;
    public function append(int $period): void;

    /**
     * @return int[]
     */
    public function getPeriodsInMicroseconds(): array;

    /**
     * @return \Iterator<int, int>
     */
    public function getIterator(): \Iterator;
}
