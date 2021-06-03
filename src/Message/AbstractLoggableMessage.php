<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Message;

use Psr\Http\Message\MessageInterface;

abstract class AbstractLoggableMessage implements \JsonSerializable
{
    public const KEY_HEADERS = 'headers';
    public const KEY_BODY = 'body';

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        $message = $this->getMessage();

        if ($message instanceof MessageInterface) {
            $loggableBody = $message->getBody();
            $loggableBody->rewind();

            return [
                self::KEY_HEADERS => $message->getHeaders(),
                self::KEY_BODY => $loggableBody->getContents()
            ];
        }

        return [];
    }

    abstract protected function getMessage(): ?MessageInterface;
}
