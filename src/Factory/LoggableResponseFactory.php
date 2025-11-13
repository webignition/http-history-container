<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Factory;

use GuzzleHttp\Psr7\Response;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableResponseFactory
{
    public const KEY_STATUS_CODE = 'status_code';

    private const DEFAULT_EMPTY_STATUS_CODE = 0;

    public static function createFromJson(string $request): LoggableResponse
    {
        $data = json_decode($request, true);
        $data = is_array($data) ? $data : [];

        $statusCode = $data[self::KEY_STATUS_CODE] ?? self::DEFAULT_EMPTY_STATUS_CODE;
        if (!is_int($statusCode)) {
            $statusCode = self::DEFAULT_EMPTY_STATUS_CODE;
        }

        return new LoggableResponse(
            new Response(
                $statusCode,
                MessageComponentExtractor::extractHeaders($data),
                MessageComponentExtractor::extractBody($data),
            )
        );
    }
}
