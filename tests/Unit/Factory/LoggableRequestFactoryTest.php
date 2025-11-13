<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Factory;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\HttpHistoryContainer\Factory\LoggableRequestFactory;
use webignition\HttpHistoryContainer\Message\LoggableRequest;

class LoggableRequestFactoryTest extends TestCase
{
    #[DataProvider('createFromJsonDataProvider')]
    public function testCreateFromJson(string $serializedRequest, LoggableRequest $expectedLoggableRequest): void
    {
        $loggableRequest = LoggableRequestFactory::createFromJson($serializedRequest);
        $request = $loggableRequest->getRequest();
        $expectedRequest = $expectedLoggableRequest->getRequest();

        self::assertSame($expectedRequest->getMethod(), $request->getMethod());
        self::assertSame((string) $expectedRequest->getUri(), (string) $request->getUri());
        self::assertSame($expectedRequest->getHeaders(), $request->getHeaders());
        self::assertSame($expectedRequest->getBody()->getContents(), $request->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public static function createFromJsonDataProvider(): array
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
            'GET req, no req headers, no req body' => [
                'serializedRequest' => json_encode([
                    'method' => 'GET',
                    'uri' => 'http://example.com/request_one',
                    'headers' => [
                        'Host' => [
                            'example.com',
                        ],
                    ],
                    'body' => '',
                ]),
                'expectedLoggableRequest' => new LoggableRequest(
                    new Request('GET', 'http://example.com/request_one')
                ),
            ],
            'GET req, no req headers, no req body, 404 response' => [
                'serializedRequest' => json_encode([
                    'method' => 'GET',
                    'uri' => 'http://example.com/request_two',
                    'headers' => [
                        'Host' => [
                            'example.com',
                        ],
                    ],
                    'body' => '',
                ]),
                'expectedLoggableRequest' => new LoggableRequest(
                    new Request('GET', 'http://example.com/request_two')
                ),
            ],
            'POST req, no req headers, no req body, 200 response' => [
                'serializedRequest' => json_encode([
                    'method' => 'POST',
                    'uri' => 'http://example.com/request_three',
                    'headers' => [
                        'Host' => [
                            'example.com',
                        ],
                    ],
                    'body' => '',
                ]),
                'expectedLoggableRequest' => new LoggableRequest(
                    new Request('POST', 'http://example.com/request_three')
                ),
            ],
            'GET req, w/ req headers, w/ req body' => [
                'serializedRequest' => json_encode([
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
                ]),
                'expectedLoggableRequest' => new LoggableRequest(
                    new Request(
                        'GET',
                        'http://example.com/request_three',
                        [
                            'content-type' => 'application/json',
                        ],
                        $encodedJsonBody
                    )
                ),
            ],
        ];
    }
}
