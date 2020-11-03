<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

/**
 * @implements \ArrayAccess<int, mixed>
 * @implements \Iterator<mixed>
 */
class Container implements \ArrayAccess, \Iterator, \Countable
{
    public const OFFSET_INVALID_MESSAGE = 'Invalid offset; must be an integer or null';
    public const OFFSET_INVALID_CODE = 1;

    /**
     * @var HttpTransaction[]
     */
    private array $container = [];

    private int $iteratorIndex = 0;

    /**
     * @return HttpTransaction[]
     */
    public function getTransactions(): array
    {
        return $this->container;
    }

    /**
     * @param mixed $offset
     * @param mixed $httpTransactionData
     *
     * @throws InvalidTransactionException
     */
    public function offsetSet($offset, $httpTransactionData): void
    {
        $this->validateOffset($offset);
        $httpTransaction = HttpTransaction::fromArray($httpTransactionData);

        if (is_null($offset)) {
            $this->container[] = $httpTransaction;
        } else {
            $this->container[$offset] = $httpTransaction;
        }
    }

    public function offsetExists($offset): bool
    {
        return null !== $this->offsetGet($offset);
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
     * @return HttpTransaction
     */
    public function current(): HttpTransaction
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

    public function getRequests(): RequestCollection
    {
        $requests = [];
        array_walk($this->container, function (HttpTransaction $transaction) use (&$requests) {
            $requests[] = $transaction->getRequest();
        });

        return new RequestCollection($requests);
    }

    public function getResponses(): ResponseCollection
    {
        $responses = [];
        array_walk($this->container, function (HttpTransaction $transaction) use (&$responses) {
            $responses[] = $transaction->getResponse();
        });

        return new ResponseCollection($responses);
    }

    /**
     * @return UriInterface[]
     */
    public function getRequestUrls(): array
    {
        $requestUrls = [];
        foreach ($this->getRequests() as $request) {
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
            if ($response instanceof ResponseInterface) {
                $statusCode = $response->getStatusCode();

                if ($statusCode <= 300 || $statusCode >= 400) {
                    return true;
                }
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
            $request = $httpTransaction->getRequest();

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

        $groups[] = $currentGroup;

        return $groups;
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
}
