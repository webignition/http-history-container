<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

class LoggableRequest extends AbstractLoggableMessage
{
    public const KEY_METHOD = 'method';
    public const KEY_URI = 'uri';

    private RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    protected function getMessage(): MessageInterface
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
}
