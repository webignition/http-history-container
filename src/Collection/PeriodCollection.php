<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

class PeriodCollection implements PeriodCollectionInterface
{
    private const MICROSECONDS_PER_SECOND = 1000000;

    /**
     * @var int[]
     */
    private array $periodsInMicroseconds = [];
    private bool $isInitialised = false;
    private float $lastTimestampInMicroseconds = 0;

    public function add(): void
    {
        $current = microtime(true);

        if (false === $this->isInitialised) {
            $period = 0;
            $this->isInitialised = true;
        } else {
            $period = $this->createPeriod($current);
        }

        $this->periodsInMicroseconds[] = $period;
        $this->lastTimestampInMicroseconds = $current;
    }

    public function append(int $period): void
    {
        $current = microtime(true);

        $this->periodsInMicroseconds[] = $period;
        $this->lastTimestampInMicroseconds = $current;
    }

    /**
     * @return int[]
     */
    public function getPeriodsInMicroseconds(): array
    {
        return $this->periodsInMicroseconds;
    }

    public function count(): int
    {
        return count($this->periodsInMicroseconds);
    }

    /**
     * @return \Iterator<int, int>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->periodsInMicroseconds);
    }

    private function createPeriod(float $currentTime): int
    {
        $periodAsSeconds = $currentTime - $this->lastTimestampInMicroseconds;
        $periodAsMicroseconds = $periodAsSeconds * self::MICROSECONDS_PER_SECOND;

        return (int) round($periodAsMicroseconds);
    }
}
