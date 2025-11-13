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

        return new LoggableRequest(
            new Request(
                MessageComponentExtractor::extractStringComponent('method', $data),
                MessageComponentExtractor::extractStringComponent('uri', $data),
                MessageComponentExtractor::extractHeaders($data),
                MessageComponentExtractor::extractBody($data),
            )
        );
    }
}
