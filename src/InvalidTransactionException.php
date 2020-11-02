<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class InvalidTransactionException extends \Exception
{
    public const VALUE_REQUEST_NOT_REQUEST_MESSAGE = 'data[\'request\'] must implement ' . RequestInterface::class;
    public const VALUE_REQUEST_NOT_REQUEST_CODE = 1;

    public const VALUE_RESPONSE_NOT_RESPONSE_MESSAGE =
        'data[\'response\'] must be null or implement ' . ResponseInterface::class;
    public const VALUE_RESPONSE_NOT_RESPONSE_CODE = 2;

    /**
     * @var array<mixed>
     */
    private array $data;

    /**
     * @param string $message
     * @param int $code
     * @param array<mixed> $data
     */
    public function __construct(string $message, int $code, array $data)
    {
        parent::__construct($message, $code);

        $this->data = $data;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<mixed> $data
     *
     * @return self
     */
    public static function createForInvalidRequest(array $data): self
    {
        return new InvalidTransactionException(
            self::VALUE_REQUEST_NOT_REQUEST_MESSAGE,
            self::VALUE_REQUEST_NOT_REQUEST_CODE,
            $data
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return self
     */
    public static function createForInvalidResponse(array $data): self
    {
        return new InvalidTransactionException(
            self::VALUE_RESPONSE_NOT_RESPONSE_MESSAGE,
            self::VALUE_RESPONSE_NOT_RESPONSE_CODE,
            $data
        );
    }
}
