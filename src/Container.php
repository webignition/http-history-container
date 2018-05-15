<?php

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Container implements ContainerInterface, \ArrayAccess, \Iterator, \Countable
{
    const KEY_REQUEST = 'request';
    const KEY_RESPONSE = 'response';
    const KEY_ERROR = 'error';
    const KEY_OPTIONS = 'options';

    const OFFSET_INVALID_MESSAGE = 'Invalid offset; must be an integer or null';
    const OFFSET_INVALID_CODE = 1;

    const VALUE_NOT_ARRAY_MESSAGE = 'HTTP transaction must be an array';
    const VALUE_NOT_ARRAY_CODE = 2;
    const VALUE_MISSING_KEY_MESSAGE = 'Key "%s" must be present';
    const VALUE_MISSING_KEY_CODE = 3;
    const VALUE_REQUEST_NOT_REQUEST_MESSAGE = 'Transaction[\'request\'] must implement ' . RequestInterface::class;
    const VALUE_REQUEST_NOT_REQUEST_CODE = 4;
    const VALUE_RESPONSE_NOT_RESPONSE_MESSAGE = 'Transaction[\'response\'] must implement ' . ResponseInterface::class;
    const VALUE_RESPONSE_NOT_RESPONSE_CODE = 5;

    /**
     * @var array
     */
    private $container = [];

    /**
     * @var int
     */
    private $iteratorIndex = 0;

    /**
     * @param mixed $offset
     * @param array $httpTransaction
     */
    public function offsetSet($offset, $httpTransaction)
    {
        $this->validateOffset($offset);
        $this->validateHttpTransaction($httpTransaction);

        if (is_null($offset)) {
            $this->container[] = $httpTransaction;
        } else {
            $this->container[$offset] = $httpTransaction;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iteratorIndex = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->container[$this->iteratorIndex];
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iteratorIndex;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->iteratorIndex;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return isset($this->container[$this->iteratorIndex]);
    }

    /**
     * @return RequestInterface[]
     */
    public function getRequests()
    {
        $requests = [];

        foreach ($this->container as $transaction) {
            $requests[] = $transaction[self::KEY_REQUEST];
        }

        return $requests;
    }

    /**
     * @return ResponseInterface[]
     */
    public function getResponses()
    {
        $responses = [];

        foreach ($this->container as $transaction) {
            $responses[] = $transaction[self::KEY_RESPONSE];
        }

        return $responses;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestUrls()
    {
        $requestUrls = [];

        $requests = $this->getRequests();
        foreach ($requests as $request) {
            $requestUrls[] = $request->getUri();
        }

        return $requestUrls;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestUrlsAsStrings()
    {
        /* @var string[] $requestUrls */
        $requestUrls = $this->getRequestUrls();

        array_walk($requestUrls, function (&$item) {
            $item = (string)$item;
        });

        return $requestUrls;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastRequest()
    {
        return $this->getLastArrayValue($this->getRequests());
    }

    /**
     * {@inheritdoc}
     */
    public function getLastRequestUrl()
    {
        $lastRequest = $this->getLastRequest();

        if (empty($lastRequest)) {
            return null;
        }

        return $lastRequest->getUri();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastResponse()
    {
        return $this->getLastArrayValue($this->getResponses());
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->container);
    }

    public function clear()
    {
        $this->container = [];
        $this->iteratorIndex = 0;
    }

    /**
     * @param array $items
     *
     * @return mixed|null
     */
    private function getLastArrayValue(array $items)
    {
        if (empty($items)) {
            return null;
        }

        return array_pop($items);
    }

    /**
     * @param mixed $offset
     */
    private function validateOffset($offset)
    {
        if (!(is_null($offset) || is_int($offset))) {
            throw new \InvalidArgumentException(
                self::OFFSET_INVALID_MESSAGE,
                self::OFFSET_INVALID_CODE
            );
        }
    }

    /**
     * @param array $httpTransaction
     */
    private function validateHttpTransaction($httpTransaction)
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