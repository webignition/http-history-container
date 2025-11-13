<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;

class MiddlewareFactory
{
    public static function create(Container &$container): callable
    {
        return static function (callable $handler) use (&$container): callable {
            return static function (RequestInterface $request, array $options) use ($handler, &$container) {
                return $handler($request, $options)->then(
                    static function ($value) use ($request, &$container, $options) {
                        $container[] = [
                            'request' => $request,
                            'response' => $value,
                            'error' => null,
                            'options' => $options,
                        ];

                        return $value;
                    },
                    static function ($reason) use ($request, &$container, $options) {
                        $container[] = [
                            'request' => $request,
                            'response' => null,
                            'error' => $reason,
                            'options' => $options,
                        ];

                        if ($reason instanceof PromiseInterface) {
                            return $reason;
                        }

                        return new RejectedPromise($reason);
                    }
                );
            };
        };
    }
}
