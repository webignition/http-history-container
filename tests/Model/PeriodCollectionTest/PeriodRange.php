<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Model\PeriodCollectionTest;

class PeriodRange
{
    private int $lower;
    private ?int $upper;

    public function __construct(int $lower, ?int $upper = null)
    {
        $this->lower = $lower;
        $this->upper = $upper;
    }

    public function getLower(): int
    {
        return $this->lower;
    }

    public function getUpper(): ?int
    {
        return $this->upper;
    }
}
