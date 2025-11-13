<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Factory;

use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\HttpHistoryContainer\Transaction\LoggableTransaction;

class LoggableTransactionFactory
{
    public static function createFromJson(string $transaction): LoggableTransaction
    {
        $data = json_decode($transaction, true);
        $data = is_array($data) ? $data : [];

        $requestData = $data['request'] ?? [];
        if (!is_array($requestData)) {
            $requestData = [];
        }

        $responseData = $data['response'] ?? [];
        if (!is_array($responseData)) {
            $responseData = [];
        }

        $loggableRequest = LoggableRequestFactory::createFromJson((string) json_encode($requestData));
        $loggableResponse = LoggableResponseFactory::createFromJson((string) json_encode($responseData));
        $period = (int) ($data['period'] ?? 0);

        return new LoggableTransaction(
            new HttpTransaction(
                $loggableRequest->getRequest(),
                $loggableResponse->getResponse(),
                null,
                []
            ),
            $period
        );
    }
}
