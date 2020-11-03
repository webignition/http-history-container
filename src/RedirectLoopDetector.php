<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use Psr\Http\Message\ResponseInterface;
use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;

class RedirectLoopDetector
{
    private HttpTransactionCollection $transactions;

    public function __construct(HttpTransactionCollection $transactions)
    {
        $this->transactions = $transactions;
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
        foreach ($this->transactions->getResponses() as $response) {
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

        foreach ($this->transactions->getRequests() as $request) {
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
}
