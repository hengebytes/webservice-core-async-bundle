<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Cache\Middleware\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Cache\CacheManager;
use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;

readonly class RequestUnlockResponseModifier implements ResponseModificationInterface
{
    public function __construct(private CacheManager $cacheManager)
    {
    }

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                $this->cacheManager->unlockWSRequest($parsedResponse->mainAsyncResponse->WSRequest);
            }
        ));
    }

    public function supports(AsyncResponse $response): bool
    {
        return !$response->isCached && $response->WSRequest->isCachable();
    }

    public static function getPriority(): int
    {
        return -210;
    }
}