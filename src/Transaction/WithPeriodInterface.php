<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Transaction;

interface WithPeriodInterface
{
    public function getPeriod(): int;
}
