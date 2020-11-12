<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Transaction;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\InvalidTransactionException;
use webignition\HttpHistoryContainer\Transaction\HttpTransaction;

class HttpTransactionTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param mixed $error
     * @param array<mixed> $options
     */
    public function testCreate(RequestInterface $request, ?ResponseInterface $response, $error, array $options)
    {
        $transaction = new HttpTransaction($request, $response, $error, $options);

        self::assertSame($request, $transaction->getRequest());
        self::assertSame($response, $transaction->getResponse());
        self::assertSame($error, $transaction->getError());
        self::assertSame($options, $transaction->getOptions());
    }

    public function createDataProvider(): array
    {
        return [
            'response set' => [
                'request' => \Mockery::mock(RequestInterface::class),
                'response' => \Mockery::mock(ResponseInterface::class),
                'error' => null,
                'options' => [],
            ],
            'response not set' => [
                'request' => \Mockery::mock(RequestInterface::class),
                'response' => null,
                'error' => null,
                'options' => [],
            ],
            'error set' => [
                'request' => \Mockery::mock(RequestInterface::class),
                'response' => null,
                'error' => 'error',
                'options' => [],
            ],
        ];
    }

    /**
     * @dataProvider createFromArrayThrowsExceptionDataProvider
     *
     * @param array<mixed> $data
     * @param InvalidTransactionException $expectedException
     */
    public function testCreateFromArrayThrowsException(array $data, InvalidTransactionException $expectedException)
    {
        self::expectExceptionObject($expectedException);

        HttpTransaction::fromArray($data);
    }

    public function createFromArrayThrowsExceptionDataProvider(): array
    {
        $validRequest = \Mockery::mock(RequestInterface::class);
        $validResponse = \Mockery::mock(ResponseInterface::class);

        return [
            'request not a RequestInterface' => [
                'data' => [
                    'request' => 'not a RequestInterface',
                    'response' => $validResponse,
                ],
                'expectedException' => new InvalidTransactionException(
                    InvalidTransactionException::VALUE_REQUEST_NOT_REQUEST_MESSAGE,
                    InvalidTransactionException::VALUE_REQUEST_NOT_REQUEST_CODE,
                    [
                        'request' => 'not a RequestInterface',
                        'response' => $validResponse,
                    ]
                ),
            ],
            'response neither null nor ResponseInterface' => [
                'data' => [
                    'request' => $validRequest,
                    'response' => 'not null, not ResponseInterface',
                ],
                'expectedException' => new InvalidTransactionException(
                    InvalidTransactionException::VALUE_RESPONSE_NOT_RESPONSE_MESSAGE,
                    InvalidTransactionException::VALUE_RESPONSE_NOT_RESPONSE_CODE,
                    [
                        'request' => $validRequest,
                        'response' => 'not null, not ResponseInterface',
                    ]
                ),
            ],
        ];
    }
}
