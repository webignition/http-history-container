<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer;

use webignition\HttpHistoryContainer\Collection\HttpTransactionCollection;

class RedirectLoopDetector
{
    public function __construct(private HttpTransactionCollection $transactions)
    {
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
