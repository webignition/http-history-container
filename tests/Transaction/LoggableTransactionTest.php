<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Transaction;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use webignition\HttpHistoryContainer\Message\LoggableRequest;
use webignition\HttpHistoryContainer\Message\LoggableResponse;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\HttpHistoryContainer\Transaction\LoggableTransaction;

class LoggableTransactionTest extends TestCase
{
    /**
     * @dataProvider jsonSerializeDataProvider
     *
     * @param LoggableTransaction $transaction
     * @param array<mixed> $expectedSerializedData
     */
    public function testJsonSerialize(LoggableTransaction $transaction, array $expectedSerializedData)
    {
        self::assertEquals($expectedSerializedData, $transaction->jsonSerialize());
    }

    public function jsonSerializeDataProvider(): array
    {
        $request = new Request('GET', 'http://example.com/request_one');
        $response = new Response();

        return [
            'GET req, no req headers, no req body, 200 response, no resp headers, no resp body' => [
                'transaction' => new LoggableTransaction(
                    new HttpTransaction(
                        new Request('GET', 'http://example.com/request_one'),
                        new Response(),
                        null,
                        []
                    )
                ),
                'expectedSerializedData' => [
                    'request' => new LoggableRequest($request),
                    'response' => new LoggableResponse($response),
                ],
            ],
        ];
    }

    /**
     * @dataProvider fromJsonDataProvider
     */
    public function testFromJson(string $serializedTransaction, LoggableTransaction $expectedLoggableTransaction)
    {
        $loggableTransaction = LoggableTransaction::fromJson($serializedTransaction);

        self::assertEquals($expectedLoggableTransaction, $loggableTransaction);
    }

    public function fromJsonDataProvider(): array
    {
        $request = new Request('GET', 'http://example.com/request_one');
        $response = new Response();

        return [
            'GET req, no req headers, no req body, 200 response, no resp headers, no resp body' => [
                'serializedTransaction' => json_encode([
                    'request' => new LoggableRequest($request),
                    'response' => new LoggableResponse($response),
                ]),
                'transaction' => new LoggableTransaction(
                    new HttpTransaction(
                        new Request('GET', 'http://example.com/request_one'),
                        new Response(),
                        null,
                        []
                    )
                ),
            ],
        ];
    }
}