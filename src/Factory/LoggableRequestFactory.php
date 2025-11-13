<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Factory;

use GuzzleHttp\Psr7\Request;
use webignition\HttpHistoryContainer\Message\LoggableRequest;

class LoggableRequestFactory
{
    public static function createFromJson(string $request): LoggableRequest
    {
        $data = json_decode($request, true);
        $data = is_array($data) ? $data : [];

        $method = $data['method'] ?? '';
        if (!is_string($method)) {
            $method = '';
        }

        $uriString = $data['uri'] ?? '';
        if (!is_string($uriString)) {
            $uriString = '';
        }

        return new LoggableRequest(
            new Request(
                $method,
                $uriString,
                MessageComponentExtractor::extractHeaders($data),
                MessageComponentExtractor::extractBody($data),
            )
        );
    }
}
