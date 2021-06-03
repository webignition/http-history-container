<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Message;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

class LoggableRequest extends AbstractLoggableMessage
{
    public const KEY_METHOD = 'method';
    public const KEY_URI = 'uri';

    private const DEFAULT_EMPTY_METHOD = '';
    private const DEFAULT_EMPTY_URI = '';
    private const DEFAULT_EMPTY_HEADERS = [];
    private const DEFAULT_EMPTY_BODY = '';

    public function __construct(private RequestInterface $request)
    {
    }

    public static function fromJson(string $request): self
    {
        $data = json_decode($request, true);

        $method = $data[self::KEY_METHOD] ?? self::DEFAULT_EMPTY_METHOD;
        if (!is_string($method)) {
            $method = self::DEFAULT_EMPTY_METHOD;
        }

        $uriString = $data[self::KEY_URI] ?? self::DEFAULT_EMPTY_URI;
        if (!is_string($uriString)) {
            $uriString = self::DEFAULT_EMPTY_URI;
        }

        $headers = $data[self::KEY_HEADERS] ?? self::DEFAULT_EMPTY_HEADERS;
        if (!is_array($headers)) {
            $headers = self::DEFAULT_EMPTY_HEADERS;
        }

        $body = $data[self::KEY_BODY] ?? self::DEFAULT_EMPTY_BODY;
        if (!is_string($body)) {
            $body = self::DEFAULT_EMPTY_BODY;
        }

        return new LoggableRequest(
            new Request(
                $method,
                $uriString,
                $headers,
                $body
            )
        );
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            [
                self::KEY_METHOD => $this->request->getMethod(),
                self::KEY_URI => (string) $this->request->getUri(),
            ],
            parent::jsonSerialize()
        );
    }

    protected function getMessage(): MessageInterface
    {
        return $this->request;
    }
}
