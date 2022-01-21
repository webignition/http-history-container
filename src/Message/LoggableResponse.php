<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Message;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

class LoggableResponse extends AbstractLoggableMessage
{
    public const KEY_STATUS_CODE = 'status_code';

    private const DEFAULT_EMPTY_STATUS_CODE = 0;
    private const DEFAULT_EMPTY_HEADERS = [];
    private const DEFAULT_EMPTY_BODY = '';

    public function __construct(private ?ResponseInterface $response)
    {
    }

    public static function fromJson(string $request): self
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
                $headers,
                $body
            )
        );
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        if ($this->response instanceof ResponseInterface) {
            return array_merge(
                [
                    self::KEY_STATUS_CODE => $this->response->getStatusCode(),
                ],
                parent::jsonSerialize()
            );
        }

        return [];
    }

    protected function getMessage(): ?MessageInterface
    {
        return $this->response;
    }
}
