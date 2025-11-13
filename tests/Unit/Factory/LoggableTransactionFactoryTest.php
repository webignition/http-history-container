<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Factory;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\HttpHistoryContainer\Factory\LoggableTransactionFactory;
use webignition\HttpHistoryContainer\Message\LoggableRequest;
use webignition\HttpHistoryContainer\Message\LoggableResponse;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;
use webignition\HttpHistoryContainer\Transaction\LoggableTransaction;

class LoggableTransactionFactoryTest extends TestCase
{
    #[DataProvider('fromJsonDataProvider')]
    public function testFromJson(string $serializedTransaction, LoggableTransaction $expectedLoggableTransaction): void
    {
        $loggableTransaction = LoggableTransactionFactory::createFromJson($serializedTransaction);

        self::assertEquals($expectedLoggableTransaction, $loggableTransaction);
    }

    /**
     * @return array<mixed>
     */
    public static function fromJsonDataProvider(): array
    {
        $request = new Request('GET', 'http://example.com/request_one');
        $response = new Response();

        return [
            'GET req, no req headers, no req body, 200 response, no resp headers, no resp body' => [
                'serializedTransaction' => json_encode([
                    'request' => new LoggableRequest($request),
                    'response' => new LoggableResponse($response),
                    'period' => 200,
                ]),
                'expectedLoggableTransaction' => new LoggableTransaction(
                    new HttpTransaction(
                        new Request('GET', 'http://example.com/request_one'),
                        new Response(),
                        null,
                        []
                    ),
                    200
                ),
            ],
        ];
    }
}
