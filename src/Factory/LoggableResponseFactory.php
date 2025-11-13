<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Factory;

use GuzzleHttp\Psr7\Response;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableResponseFactory
{
    public const KEY_STATUS_CODE = 'status_code';

    public const KEY_HEADERS = 'headers';
    public const KEY_BODY = 'body';

    private const DEFAULT_EMPTY_STATUS_CODE = 0;
    private const DEFAULT_EMPTY_HEADERS = [];
    private const DEFAULT_EMPTY_BODY = '';

    public static function createFromJson(string $request): LoggableResponse
    {
        $data = json_decode($request, true);
        $data = is_array($data) ? $data : [];

        $statusCode = $data[self::KEY_STATUS_CODE] ?? self::DEFAULT_EMPTY_STATUS_CODE;
        if (!is_int($statusCode)) {
            $statusCode = self::DEFAULT_EMPTY_STATUS_CODE;
        }

        $headers = $data[self::KEY_HEADERS] ?? self::DEFAULT_EMPTY_HEADERS;
        if (!is_array($headers)) {
            $headers = self::DEFAULT_EMPTY_HEADERS;
        }

        $body = $data[self::KEY_BODY] ?? self::DEFAULT_EMPTY_BODY;
        if (!is_string($body)) {
            $body = self::DEFAULT_EMPTY_BODY;
        }

        return new LoggableResponse(
            new Response(
                $statusCode,
                self::extractHeaderData($data),
                $body
            )
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<array<string>|string>
     */
    private static function extractHeaderData(array $data): array
    {
        $headers = $data[self::KEY_HEADERS] ?? self::DEFAULT_EMPTY_HEADERS;
        if (!is_array($headers)) {
            return self::DEFAULT_EMPTY_HEADERS;
        }

        $filteredHeaders = self::extractStringValues($headers);

        foreach ($headers as $key => $header) {
            if (is_array($header)) {
                $headerStringValues = self::extractStringValues($header);

                if ([] !== $headerStringValues) {
                    $filteredHeaders[$key] = $headerStringValues;
                }
            }
        }

        return $filteredHeaders;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<string>
     */
    private static function extractStringValues(array $data): array
    {
        return array_filter($data, function ($value) {
            return is_string($value);
        });
    }
}
