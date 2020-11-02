<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\HttpTransaction;

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
}
