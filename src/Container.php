<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @implements \ArrayAccess<int, mixed>
 * @implements \Iterator<mixed>
 */
class Container implements \ArrayAccess, \Iterator, \Countable
{
    public const KEY_REQUEST = 'request';
    public const KEY_RESPONSE = 'response';
    public const KEY_ERROR = 'error';
    public const KEY_OPTIONS = 'options';

    public const OFFSET_INVALID_MESSAGE = 'Invalid offset; must be an integer or null';
    public const OFFSET_INVALID_CODE = 1;

    public const VALUE_NOT_ARRAY_MESSAGE = 'HTTP transaction must be an array';
    public const VALUE_NOT_ARRAY_CODE = 2;
    public const VALUE_MISSING_KEY_MESSAGE = 'Key "%s" must be present';
    public const VALUE_MISSING_KEY_CODE = 3;
    public const VALUE_REQUEST_NOT_REQUEST_MESSAGE = '
    Transaction[\'request\'] must implement ' . RequestInterface::class;
    public const VALUE_REQUEST_NOT_REQUEST_CODE = 4;
    public const VALUE_RESPONSE_NOT_RESPONSE_MESSAGE =
        'Transaction[\'response\'] must implement ' . ResponseInterface::class;
    public const VALUE_RESPONSE_NOT_RESPONSE_CODE = 5;

    /**
     * @var array<array<string, RequestInterface|ResponseInterface>>
     */
    private array $container = [];

    private int $iteratorIndex = 0;

    /**
     * @return array<array<string, RequestInterface|ResponseInterface>>
     */
    public function getTransactions(): array
    {
        return $this->container;
    }

    /**
     * @param mixed $offset
     * @param array<string, RequestInterface|ResponseInterface> $httpTransaction
     */
    public function offsetSet($offset, $httpTransaction): void
    {
        $this->validateOffset($offset);
        $this->validateHttpTransaction($httpTransaction);

        if (is_null($offset)) {
            $this->container[] = $httpTransaction;
        } else {
            $this->container[$offset] = $httpTransaction;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    public function rewind(): void
    {
        $this->iteratorIndex = 0;
    }

    /**
     * @return array<string, RequestInterface|ResponseInterface>
     */
    public function current(): array
    {
        return $this->container[$this->iteratorIndex];
    }

    public function key(): int
    {
        return $this->iteratorIndex;
    }

    public function next(): void
    {
        ++$this->iteratorIndex;
    }

    public function valid(): bool
    {
        return isset($this->container[$this->iteratorIndex]);
    }

    /**
     * @return RequestInterface[]
     */
    public function getRequests(): array
    {
        $requests = [];

        foreach ($this->container as $transaction) {
            $request = $transaction[self::KEY_REQUEST] ?? null;

            if ($request instanceof RequestInterface) {
                $requests[] = $request;
            }
        }

        return $requests;
    }

    /**
     * @return ResponseInterface[]
     */
    public function getResponses(): array
    {
        $responses = [];

        foreach ($this->container as $transaction) {
            $response = $transaction[self::KEY_RESPONSE] ?? null;

            if ($response instanceof ResponseInterface) {
                $responses[] = $response;
            }
        }

        return $responses;
    }

    /**
     * @return UriInterface[]
     */
    public function getRequestUrls(): array
    {
        $requestUrls = [];

        $requests = $this->getRequests();
        foreach ($requests as $request) {
            $requestUrls[] = $request->getUri();
        }

        return $requestUrls;
    }

    /**
     * @return string[]
     */
    public function getRequestUrlsAsStrings(): array
    {
        $requestUrlStrings = [];

        foreach ($this->getRequestUrls() as $requestUrl) {
            $requestUrlStrings[] = (string) $requestUrl;
        }

        return $requestUrlStrings;
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->getLastArrayValue($this->getRequests());
    }

    public function getLastRequestUrl(): ?UriInterface
    {
        $lastRequest = $this->getLastRequest();

        if (empty($lastRequest)) {
            return null;
        }

        return $lastRequest->getUri();
    }

    public function getLastResponse(): ?ResponseInterface
    {
        return $this->getLastArrayValue($this->getResponses());
    }

    public function count(): int
    {
        return count($this->container);
    }

    public function clear(): void
    {
        $this->container = [];
        $this->iteratorIndex = 0;
    }

    public function hasRedirectLoop(): bool
    {
        if ($this->containsAnyNonRedirectResponses()) {
            return false;
        }

        $urlGroups = $this->createUrlGroupsByMethodChange();

        foreach ($urlGroups as $urlGroup) {
            if ($this->doesUrlSetHaveRedirectLoop($urlGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $urls
     *
     * @return bool
     */
    private function doesUrlSetHaveRedirectLoop(array $urls): bool
    {
        foreach ($urls as $urlIndex => $url) {
            if (in_array($url, array_slice($urls, $urlIndex + 1))) {
                return true;
            }
        }

        return false;
    }

    private function containsAnyNonRedirectResponses(): bool
    {
        foreach ($this->getResponses() as $response) {
            $statusCode = $response->getStatusCode();

            if ($statusCode <= 300 || $statusCode >= 400) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function createUrlGroupsByMethodChange(): array
    {
        $currentMethod = null;
        $groups = [];
        $currentGroup = [];

        foreach ($this->container as $httpTransaction) {
            $request = $httpTransaction[self::KEY_REQUEST] ?? null;

            if ($request instanceof RequestInterface) {
                $method = $request->getMethod();

                if (null === $currentMethod) {
                    $currentMethod = $method;
                }

                if ($method !== $currentMethod) {
                    $groups[] = $currentGroup;
                    $currentGroup = [];
                    $currentMethod = $method;
                }

                $currentGroup[] = (string) $request->getUri();
            }
        }

        $groups[] = $currentGroup;

        return $groups;
    }

    /**
     * @param array<mixed> $items
     *
     * @return mixed|null
     */
    private function getLastArrayValue(array $items)
    {
        return array_pop($items);
    }

    /**
     * @param mixed $offset
     */
    private function validateOffset($offset): void
    {
        if (!(is_null($offset) || is_int($offset))) {
            throw new \InvalidArgumentException(
                self::OFFSET_INVALID_MESSAGE,
                self::OFFSET_INVALID_CODE
            );
        }
    }

    /**
     * @param array<mixed> $httpTransaction
     */
    private function validateHttpTransaction($httpTransaction): void
    {
        if (!is_array($httpTransaction)) {
            throw new \InvalidArgumentException(
                self::VALUE_NOT_ARRAY_MESSAGE,
                self::VALUE_NOT_ARRAY_CODE
            );
        }

        $requiredKeys = [
            self::KEY_REQUEST,
            self::KEY_RESPONSE,
            self::KEY_ERROR,
            self::KEY_OPTIONS,
        ];

        foreach ($requiredKeys as $requiredKey) {
            if (!array_key_exists($requiredKey, $httpTransaction)) {
                throw new \InvalidArgumentException(
                    sprintf(self::VALUE_MISSING_KEY_MESSAGE, $requiredKey),
                    self::VALUE_MISSING_KEY_CODE
                );
            }
        }

        if (!$httpTransaction[self::KEY_REQUEST] instanceof RequestInterface) {
            throw new \InvalidArgumentException(
                self::VALUE_REQUEST_NOT_REQUEST_MESSAGE,
                self::VALUE_REQUEST_NOT_REQUEST_CODE
            );
        }

        $response = $httpTransaction[self::KEY_RESPONSE];

        if (!empty($response) && !$response instanceof ResponseInterface) {
            throw new \InvalidArgumentException(
                self::VALUE_RESPONSE_NOT_RESPONSE_MESSAGE,
                self::VALUE_RESPONSE_NOT_RESPONSE_CODE
            );
        }
    }
}
