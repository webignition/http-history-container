<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Factory;

use GuzzleHttp\Psr7\Response;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableResponseFactory
{
    public const KEY_STATUS_CODE = 'status_code';

    public const KEY_BODY = 'body';

    private const DEFAULT_EMPTY_STATUS_CODE = 0;
    private const DEFAULT_EMPTY_BODY = '';

    public static function createFromJson(string $request): LoggableResponse
    {
        $data = json_decode($request, true);
        $data = is_array($data) ? $data : [];

        $statusCode = $data[self::KEY_STATUS_CODE] ?? self::DEFAULT_EMPTY_STATUS_CODE;
        if (!is_int($statusCode)) {
            $statusCode = self::DEFAULT_EMPTY_STATUS_CODE;
        }

        $body = $data[self::KEY_BODY] ?? self::DEFAULT_EMPTY_BODY;
        if (!is_string($body)) {
            $body = self::DEFAULT_EMPTY_BODY;
        }

        return new LoggableResponse(
            new Response(
                $statusCode,
                HeaderExtractor::extract($data),
                $body
            )
        );
    }
}
