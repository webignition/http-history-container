<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Log\LoggerInterface;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\HttpHistoryContainer\Transaction\LoggableTransaction;

class LoggableContainer extends Container
{
    public function __construct(private LoggerInterface $logger)
    {
        parent::__construct();
    }

    /**
     * @throws InvalidTransactionException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        parent::offsetSet($offset, $value);

        $collection = $this->getTransactions();
        $transactions = $collection->getTransactions();
        $periods = $collection->getPeriods()->getPeriodsInMicroseconds();

        $currentTransaction = array_pop($transactions);
        $period = (int) array_pop($periods);

        if ($currentTransaction instanceof HttpTransaction) {
            $this->logTransaction($currentTransaction, $period);
        }
    }

    private function logTransaction(HttpTransaction $transaction, int $period): void
    {
        $loggableTransaction = new LoggableTransaction($transaction, $period);

        $this->logger->debug((string) json_encode($loggableTransaction));
    }
}
