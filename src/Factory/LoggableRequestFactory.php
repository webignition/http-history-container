<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Factory;

use GuzzleHttp\Psr7\Request;
use webignition\HttpHistoryContainer\Message\LoggableRequest;

class LoggableRequestFactory
{
    public const KEY_METHOD = 'method';
    public const KEY_URI = 'uri';

    public const KEY_HEADERS = 'headers';
    public const KEY_BODY = 'body';

    private const DEFAULT_EMPTY_METHOD = '';
    private const DEFAULT_EMPTY_URI = '';
    private const DEFAULT_EMPTY_HEADERS = [];
    private const DEFAULT_EMPTY_BODY = '';

    public static function createFromJson(string $request): LoggableRequest
    {
        $data = json_decode($request, true);
        $data = is_array($data) ? $data : [];

        $method = $data[self::KEY_METHOD] ?? self::DEFAULT_EMPTY_METHOD;
        if (!is_string($method)) {
            $method = self::DEFAULT_EMPTY_METHOD;
        }

        $uriString = $data[self::KEY_URI] ?? self::DEFAULT_EMPTY_URI;
        if (!is_string($uriString)) {
            $uriString = self::DEFAULT_EMPTY_URI;
        }

        $body = $data[self::KEY_BODY] ?? self::DEFAULT_EMPTY_BODY;
        if (!is_string($body)) {
            $body = self::DEFAULT_EMPTY_BODY;
        }

        return new LoggableRequest(
            new Request(
                $method,
                $uriString,
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
