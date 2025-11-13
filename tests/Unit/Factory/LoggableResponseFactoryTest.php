<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Factory;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Factory\LoggableResponseFactory;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableResponseFactoryTest extends TestCase
{
    #[DataProvider('createFromJsonDataProvider')]
    public function testCreateFromJson(string $serializedResponse, LoggableResponse $expectedLoggableResponse): void
    {
        $loggableResponse = LoggableResponseFactory::createFromJson($serializedResponse);
        $response = $loggableResponse->getResponse();
        self::assertInstanceOf(ResponseInterface::class, $response);

        $expectedResponse = $expectedLoggableResponse->getResponse();
        self::assertInstanceOf(ResponseInterface::class, $expectedResponse);

        self::assertSame($expectedResponse->getStatusCode(), $response->getStatusCode());
        self::assertSame($expectedResponse->getHeaders(), $response->getHeaders());
        self::assertSame($expectedResponse->getBody()->getContents(), $response->getBody()->getContents());
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
