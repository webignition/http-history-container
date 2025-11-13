<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Factory;

class MessageComponentExtractor
{
    /**
     * @param array<mixed> $data
     *
     * @return array<array<string>|string>
     */
    public static function extractHeaders(array $data): array
    {
        $headers = $data['headers'] ?? [];
        if (!is_array($headers)) {
            return [];
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
     */
    public static function extractBody(array $data): string
    {
        $body = $data['body'] ?? '';
        if (!is_string($body)) {
            $body = '';
        }

        return $body;
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
