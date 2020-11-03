<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Log\LoggerInterface;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\HttpHistoryContainer\Transaction\LoggableTransaction;

class LoggableContainer extends Container
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct();

        $this->logger = $logger;
    }

    public function offsetSet($offset, $httpTransactionData): void
    {
        parent::offsetSet($offset, $httpTransactionData);

        $collection = $this->getTransactions();
        $transactions = $collection->getTransactions();

        $currentTransaction = array_pop($transactions);

        if ($currentTransaction instanceof HttpTransaction) {
            $this->logTransaction($currentTransaction);
        }
    }

    private function logTransaction(HttpTransaction $transaction): void
    {
        $loggableTransaction = new LoggableTransaction($transaction);

        $this->logger->debug((string) json_encode($loggableTransaction));
    }
}
