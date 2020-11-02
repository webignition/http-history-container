<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Message;

use Psr\Http\Message\MessageInterface;

abstract class AbstractLoggableMessage implements \JsonSerializable
{
    abstract protected function getMessage(): ?MessageInterface;

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
                'headers' => $message->getHeaders(),
                'body' => $loggableBody->getContents()
            ];
        }

        return [];
    }
}
