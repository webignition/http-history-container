<?php

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Container implements ContainerInterface, \ArrayAccess
{
    const KEY_REQUEST = 'request';
    const KEY_RESPONSE = 'response';
    const KEY_ERROR = 'error';
    const KEY_OPTIONS = 'options';

    const OFFSET_INVALID_MESSAGE = 'Invalid offset; must be an integer or null';
    const OFFSET_INVALID_CODE = 1;

    const VALUE_NOT_ARRAY_MESSAGE = 'Value must be an array';
    const VALUE_NOT_ARRAY_CODE = 2;
    const VALUE_MISSING_KEY_MESSAGE = 'Key "%s" must be present';
    const VALUE_MISSING_KEY_CODE = 3;
    const VALUE_REQUEST_NOT_REQUEST_MESSAGE = 'Value[\'request\'] must implement ' . RequestInterface::class;
    const VALUE_REQUEST_NOT_REQUEST_CODE = 4;
    const VALUE_RESPONSE_NOT_RESPONSE_MESSAGE = 'Value[\'response\'] must implement ' . ResponseInterface::class;
    const VALUE_RESPONSE_NOT_RESPONSE_CODE = 5;

    /**
     * @var array
     */
    private $container = [];

    /**
     * @param mixed $offset
     * @param array $value
     */
    public function offsetSet($offset, $value)
    {
        $this->validateOffset($offset);
        $this->validateValue($value);

        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
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
     * @return UriInterface[]
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
     * @return string[]
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
     * @return RequestInterface
     */
    public function getLastRequest()
    {
        $requests = $this->getRequests();

        if (empty($requests)) {
            return null;
        }

        return array_pop($requests);
    }

    /**
     * @return UriInterface
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
     * @param mixed $value
     */
    private function validateValue($value)
    {
        if (!is_array($value)) {
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
            if (!array_key_exists($requiredKey, $value)) {
                throw new \InvalidArgumentException(
                    sprintf(self::VALUE_MISSING_KEY_MESSAGE, $requiredKey),
                    self::VALUE_MISSING_KEY_CODE
                );
            }
        }

        if (!$value[self::KEY_REQUEST] instanceof RequestInterface) {
            throw new \InvalidArgumentException(
                self::VALUE_REQUEST_NOT_REQUEST_MESSAGE,
                self::VALUE_REQUEST_NOT_REQUEST_CODE
            );
        }

        if (!$value[self::KEY_RESPONSE] instanceof ResponseInterface) {
            throw new \InvalidArgumentException(
                self::VALUE_RESPONSE_NOT_RESPONSE_MESSAGE,
                self::VALUE_RESPONSE_NOT_RESPONSE_CODE
            );
        }
    }
}
