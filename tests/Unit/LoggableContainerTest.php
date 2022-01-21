<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use webignition\HttpHistoryContainer\LoggableContainer;
use webignition\HttpHistoryContainer\Transaction\LoggableTransaction;

class LoggableContainerTest extends TestCase
{
    /**
     * @var resource
     */
    private $stream;

    private LoggableContainer $container;

    protected function setUp(): void
    {
        parent::setUp();

        $stream = fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        if (is_resource($stream)) {
            $this->stream = $stream;
        }

        $logger = new Logger('');
        $logHandler = new StreamHandler($this->stream);
        $logHandler
            ->setFormatter(new LineFormatter('%message%' . "\n"))
        ;

        $logger->pushHandler($logHandler);

        $this->container = new LoggableContainer($logger);
    }

    /**
     * @dataProvider logTransactionsDataProvider
     *
     * @param array<mixed> $transactions
     * @param array<mixed> $expectedDecodedMessages
     */
    public function testLogTransactions(array $transactions, array $expectedDecodedMessages): void
    {
        foreach ($transactions as $transaction) {
            $this->container->offsetSet(null, $transaction);
        }

        rewind($this->stream);
        $streamContents = (string) stream_get_contents($this->stream);

        $loggedMessages = array_filter(
            explode("\n", $streamContents)
        );

        $decodedMessages = [];
        foreach ($loggedMessages as $loggedMessage) {
            $decodedMessages[] = json_decode($loggedMessage, true);
        }

        self::assertCount(count($expectedDecodedMessages), $decodedMessages);

        $lastPeriod = null;
        foreach ($decodedMessages as $decodedMessage) {
            self::assertIsArray($decodedMessage);
            self::assertArrayHasKey(LoggableTransaction::KEY_REQUEST, $decodedMessage);
            self::assertArrayHasKey(LoggableTransaction::KEY_RESPONSE, $decodedMessage);
            self::assertArrayHasKey(LoggableTransaction::KEY_PERIOD, $decodedMessage);

            $period = $decodedMessage[LoggableTransaction::KEY_PERIOD];

            if (null === $lastPeriod) {
                self::assertSame(0, $period);
            } else {
                self::assertGreaterThan($lastPeriod, $period);
            }

            $lastPeriod = $period;
        }

        foreach ($expectedDecodedMessages as $messageIndex => $expectedDecodedMessage) {
            self::assertIsArray($expectedDecodedMessage);
            self::assertArrayHasKey(LoggableTransaction::KEY_REQUEST, $expectedDecodedMessage);
            self::assertArrayHasKey(LoggableTransaction::KEY_RESPONSE, $expectedDecodedMessage);

            $decodedMessage = $decodedMessages[$messageIndex];
            self::assertIsArray($decodedMessage);
            self::assertArrayHasKey(LoggableTransaction::KEY_REQUEST, $decodedMessage);

            self::assertEquals(
                $expectedDecodedMessage[LoggableTransaction::KEY_REQUEST],
                $decodedMessage[LoggableTransaction::KEY_REQUEST]
            );

            self::assertEquals(
                $expectedDecodedMessage[LoggableTransaction::KEY_RESPONSE],
                $decodedMessage[LoggableTransaction::KEY_RESPONSE]
            );
        }
    }

    /**
     * @return array<mixed>
     */
    public function logTransactionsDataProvider(): array
    {
        $encodedJsonBody = (string) json_encode(
            [
                'key1' => 'value1',
                'key2' => [
                    'key2key1' => 'key2value1',
                    'key2key2' => 'key2value2',
                ],
            ],
            JSON_PRETTY_PRINT
        );

        return [
            'GET req, no req headers, no req body, 200 response, no resp headers, no resp body' => [
                'transactions' => [
                    [
                        'request' => new Request('GET', 'http://example.com/request_one'),
                        'response' => new Response(),
                    ],
                ],
                'expectedDecodedMessages' => [
                    [
                        'request' => [
                            'method' => 'GET',
                            'uri' => 'http://example.com/request_one',
                            'headers' => [
                                'Host' => [
                                    'example.com',
                                ],
                            ],
                            'body' => '',
                        ],
                        'response' => [
                            'status_code' => 200,
                            'headers' => [],
                            'body' => '',
                        ],
                    ],
                ],
            ],
            'GET req, no req headers, no req body, 404 response, no resp headers, no resp body' => [
                'transactions' => [
                    [
                        'request' => new Request('GET', 'http://example.com/request_one'),
                        'response' => new Response(404),
                    ],
                ],
                'expectedDecodedMessages' => [
                    [
                        'request' => [
                            'method' => 'GET',
                            'uri' => 'http://example.com/request_one',
                            'headers' => [
                                'Host' => [
                                    'example.com',
                                ],
                            ],
                            'body' => '',
                        ],
                        'response' => [
                            'status_code' => 404,
                            'headers' => [],
                            'body' => '',
                        ],
                    ],
                ],
            ],
            'POST req, no req headers, no req body, 200 response, no resp headers, no resp body' => [
                'transactions' => [
                    [
                        'request' => new Request('POST', 'http://example.com/request_two'),
                        'response' => new Response(),
                    ],
                ],
                'expectedDecodedMessages' => [
                    [
                        'request' => [
                            'method' => 'POST',
                            'uri' => 'http://example.com/request_two',
                            'headers' => [
                                'Host' => [
                                    'example.com',
                                ],
                            ],
                            'body' => '',
                        ],
                        'response' => [
                            'status_code' => 200,
                            'headers' => [],
                            'body' => '',
                        ],
                    ],
                ],
            ],
            'GET req, w/ req headers, w/ req body, 200 response, no resp headers, no resp body' => [
                'transactions' => [
                    [
                        'request' => new Request(
                            'GET',
                            'http://example.com/request_three',
                            [
                                'content-type' => 'application/json',
                            ],
                            $encodedJsonBody
                        ),
                        'response' => new Response(),
                    ],
                ],
                'expectedDecodedMessages' => [
                    [
                        'request' => [
                            'method' => 'GET',
                            'uri' => 'http://example.com/request_three',
                            'headers' => [
                                'Host' => [
                                    'example.com',
                                ],
                                'content-type' => [
                                    'application/json',
                                ],
                            ],
                            'body' => $encodedJsonBody,
                        ],
                        'response' => [
                            'status_code' => 200,
                            'headers' => [],
                            'body' => '',
                        ],
                    ],
                ],
            ],
            'GET req, no req headers, no req body, 200 response, w/ resp headers, w/ resp body' => [
                'transactions' => [
                    [
                        'request' => new Request(
                            'GET',
                            'http://example.com/request_three'
                        ),
                        'response' => new Response(
                            200,
                            [
                                'content-type' => 'application/json',
                            ],
                            $encodedJsonBody
                        ),
                    ],
                ],
                'expectedDecodedMessages' => [
                    [
                        'request' => [
                            'method' => 'GET',
                            'uri' => 'http://example.com/request_three',
                            'headers' => [
                                'Host' => [
                                    'example.com',
                                ],
                            ],
                            'body' => '',
                        ],
                        'response' => [
                            'status_code' => 200,
                            'headers' => [
                                'content-type' => [
                                    'application/json',
                                ],
                            ],
                            'body' => $encodedJsonBody,
                        ],
                    ],
                ],
            ],
            'Two simple GET requests' => [
                'transactions' => [
                    [
                        'request' => new Request('GET', 'http://example.com/request_one'),
                        'response' => new Response(),
                    ],
                    [
                        'request' => new Request('GET', 'http://example.com/request_two'),
                        'response' => new Response(),
                    ],
                ],
                'expectedDecodedMessages' => [
                    [
                        'request' => [
                            'method' => 'GET',
                            'uri' => 'http://example.com/request_one',
                            'headers' => [
                                'Host' => [
                                    'example.com',
                                ],
                            ],
                            'body' => '',
                        ],
                        'response' => [
                            'status_code' => 200,
                            'headers' => [],
                            'body' => '',
                        ],
                    ],
                    [
                        'request' => [
                            'method' => 'GET',
                            'uri' => 'http://example.com/request_two',
                            'headers' => [
                                'Host' => [
                                    'example.com',
                                ],
                            ],
                            'body' => '',
                        ],
                        'response' => [
                            'status_code' => 200,
                            'headers' => [],
                            'body' => '',
                        ],
                    ],
                ],
            ],
        ];
    }
}
