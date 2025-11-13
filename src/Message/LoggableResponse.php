<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

class LoggableResponse extends AbstractLoggableMessage
{
    public const KEY_STATUS_CODE = 'status_code';

    public function __construct(private ?ResponseInterface $response)
    {
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
