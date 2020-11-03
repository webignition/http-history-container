<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Collection;

use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

class InvalidTransactionOffsetException extends \Exception
{
    private HttpTransaction $transaction;
    private int $offset;

    public function __construct(HttpTransaction $transaction, int $offset)
    {
        parent::__construct('Transaction offset must be greater than zero: ' . (string) $offset);

        $this->transaction = $transaction;
        $this->offset = $offset;
    }

    public function getTransaction(): HttpTransaction
    {
        return $this->transaction;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
