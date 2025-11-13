<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Factory;

use GuzzleHttp\Psr7\Response;
use webignition\HttpHistoryContainer\Message\LoggableResponse;

class LoggableResponseFactory
{
    public static function createFromJson(string $request): LoggableResponse
    {
        $data = json_decode($request, true);
        $data = is_array($data) ? $data : [];

        $statusCode = $data['status_code'] ?? 0;
        if (!is_int($statusCode)) {
            $statusCode = 0;
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
