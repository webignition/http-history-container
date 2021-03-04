<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Model\PeriodCollectionTest;

class PeriodRange
{
    public function __construct(private int $lower, private ?int $upper = null)
    {
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
