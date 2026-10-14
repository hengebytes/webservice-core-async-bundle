<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Cache\Middleware\Response;

use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Exception\ConnectionInitException;
use Hengebytes\WebserviceCoreAsyncBundle\Exception\ResponseFailException;
use Hengebytes\WebserviceCoreAsyncBundle\Handler\AsyncRequestHandler;
use Hengebytes\WebserviceCoreAsyncBundle\Handler\BaseRequestHandler;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class ReloadLockedResponseResponseModifier implements ResponseModificationInterface
{
    public function __construct(private AsyncRequestHandler $requestHandler)
    {
    }

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            fn(ParsedResponse $parsedResponse) => self::reloadLockedResponse($parsedResponse, $this->requestHandler)
        ));
    }

    /**
     * @throws ConnectionInitException
     */
    public static function reloadLockedResponse(ParsedResponse $parsedResponse, BaseRequestHandler $requestHandler): void
    {
        $mainAsyncResponse = $parsedResponse->mainAsyncResponse;
        if ($mainAsyncResponse->WSResponse->getStatusCode() !== Response::HTTP_LOCKED) {
            return;
        }
        // Resolve the reloaded request so its own middleware runs (log, store to cache, unlock).
        // Taking only its WSResponse leaves the lock held and the response neither cached nor logged,
        // so every other waiter ends up reloading the same way.
        // toArray() (unlike getStatusCode()) lets other failures of the reload reach the caller as is.
        $reloadedResponse = $requestHandler->request($mainAsyncResponse->WSRequest);
        try {
            $reloadedResponse->toArray();
        } catch (ResponseFailException $e) {
            $parsedResponse->exception = $e;
        }
        $mainAsyncResponse->WSResponse = $reloadedResponse->WSResponse;

        // never hand the lock placeholder ({"cacheId": ...}) to the caller as a successful response
        if (!$parsedResponse->exception && $mainAsyncResponse->WSResponse->getStatusCode() === Response::HTTP_LOCKED) {
            $parsedResponse->exception = new ResponseFailException('Response is locked by another request');
        }
    }

    public function supports(AsyncResponse $response): bool
    {
        return $response->isCached && !$response->WSRequest->isSkipReadCache();
    }

    public static function getPriority(): int
    {
        return 240;
    }
}
