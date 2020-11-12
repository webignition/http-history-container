<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Message;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableResponseTest extends TestCase
{
    /**
     * @dataProvider jsonSerializeDataProvider
     *
     * @param LoggableResponse $response
     * @param array<mixed> $expectedSerializedData
     */
    public function testJsonSerialize(LoggableResponse $response, array $expectedSerializedData)
    {
        self::assertSame($expectedSerializedData, $response->jsonSerialize());
    }

    public function jsonSerializeDataProvider(): array
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
            '200 response, no resp headers, no resp body' => [
                'response' => new LoggableResponse(new Response()),
                'expectedSerializedData' => [
                    'status_code' => 200,
                    'headers' => [],
                    'body' => '',
                ],
            ],
            '404 response, no resp headers, no resp body' => [
                'response' => new LoggableResponse(new Response(404)),
                'expectedSerializedData' => [
                    'status_code' => 404,
                    'headers' => [],
                    'body' => '',
                ],
            ],
            '200 response, w/ resp headers, w/ resp body' => [
                'response' => new LoggableResponse(new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    $encodedJsonBody
                )),
                'expectedSerializedData' => [
                    'status_code' => 200,
                    'headers' => [
                        'content-type' => [
                            'application/json',
                        ],
                    ],
                    'body' => $encodedJsonBody,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createFromJsonDataProvider
     *
     * @param string $serializedResponse
     * @param LoggableResponse $expectedLoggableResponse
     */
    public function testCreateFromJson(string $serializedResponse, LoggableResponse $expectedLoggableResponse)
    {
        $loggableResponse = LoggableResponse::fromJson($serializedResponse);
        $response = $loggableResponse->getResponse();
        self::assertInstanceOf(ResponseInterface::class, $response);

        $expectedResponse = $expectedLoggableResponse->getResponse();
        self::assertInstanceOf(ResponseInterface::class, $expectedResponse);

        self::assertSame($expectedResponse->getStatusCode(), $response->getStatusCode());
        self::assertSame($expectedResponse->getHeaders(), $response->getHeaders());
        self::assertSame($expectedResponse->getBody()->getContents(), $response->getBody()->getContents());
    }

    public function createFromJsonDataProvider(): array
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
            '200 response, no resp headers, no resp body' => [
                'serializedResponse' => json_encode([
                    'status_code' => 200,
                    'headers' => [],
                    'body' => '',
                ]),
                'expectedLoggableResponse' => new LoggableResponse(new Response()),
            ],
            '404 response, no resp headers, no resp body' => [
                'serializedResponse' => json_encode([
                    'status_code' => 404,
                    'headers' => [],
                    'body' => '',
                ]),
                'expectedLoggableResponse' => new LoggableResponse(new Response(404)),
            ],
            '200 response, w/ resp headers, w/ resp body' => [
                'serializedResponse' => json_encode([
                    'status_code' => 200,
                    'headers' => [
                        'content-type' => [
                            'application/json',
                        ],
                    ],
                    'body' => $encodedJsonBody,
                ]),
                'expectedLoggableResponse' => new LoggableResponse(new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    $encodedJsonBody
                )),
            ],
        ];
    }
}
