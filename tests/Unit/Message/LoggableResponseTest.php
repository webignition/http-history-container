<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Message;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableResponseTest extends TestCase
{
    /**
     * @param array<mixed> $expectedSerializedData
     */
    #[DataProvider('jsonSerializeDataProvider')]
    public function testJsonSerialize(LoggableResponse $response, array $expectedSerializedData): void
    {
        self::assertSame($expectedSerializedData, $response->jsonSerialize());
    }

    /**
     * @return array<mixed>
     */
    public static function jsonSerializeDataProvider(): array
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
}
