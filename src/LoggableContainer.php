<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @implements \ArrayAccess<int, mixed>
 * @implements \Iterator<mixed>
 */
class LoggableContainer extends Container implements \ArrayAccess, \Iterator, \Countable
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function offsetSet($offset, $httpTransactionData): void
    {
        parent::offsetSet($offset, $httpTransactionData);

        $transactions = $this->getTransactions();
        $currentTransaction = array_pop($transactions);

        if ($currentTransaction instanceof HttpTransaction) {
            $this->logTransaction($currentTransaction);
        }
    }

    private function logTransaction(HttpTransaction $transaction): void
    {
        $loggable = [
            'request' => $this->createLoggableRequest($transaction->getRequest()),
            'response' => $this->createLoggableResponse($transaction->getResponse()),
        ];

        $this->logger->debug((string) json_encode($loggable));
    }

    /**
     * @param RequestInterface $request
     *
     * @return array<string, mixed>
     */
    private function createLoggableRequest(RequestInterface $request): array
    {
        return array_merge(
            [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
            ],
            $this->createLoggableMessage($request)
        );
    }

    /**
     * @param ResponseInterface|null $response
     *
     * @return array<string, mixed>
     */
    private function createLoggableResponse(?ResponseInterface $response): array
    {
        if ($response instanceof ResponseInterface) {
            return array_merge(
                [
                    'status_code' => $response->getStatusCode(),
                ],
                $this->createLoggableMessage($response)
            );
        }

        return [];
    }

    /**
     * @param MessageInterface $message
     *
     * @return array<string, mixed>
     */
    private function createLoggableMessage(MessageInterface $message): array
    {
        $loggableBody = $message->getBody();
        $loggableBody->rewind();

        return [
            'headers' => $message->getHeaders(),
            'body' => $loggableBody->getContents()
        ];
    }
}
