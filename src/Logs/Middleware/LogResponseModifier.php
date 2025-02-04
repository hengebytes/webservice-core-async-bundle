<?php

namespace WebserviceCoreAsyncBundle\Logs\Middleware;

use WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use WebserviceCoreAsyncBundle\Logs\MonologLogHandler;
use WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use WebserviceCoreAsyncBundle\Response\AsyncResponse;
use WebserviceCoreAsyncBundle\Response\ParsedResponse;

readonly class LogResponseModifier implements ResponseModificationInterface
{
    public function __construct(private MonologLogHandler $logHandler)
    {
    }

    public function modify(AsyncResponse $response): void
    {
        $response->addOnResponseReceivedCallback(new OnResponseReceivedCallback(
            function (ParsedResponse $parsedResponse) {
                $this->logHandler->writeLog($parsedResponse);
            }
        ));
    }

    public function supports(AsyncResponse $response): bool
    {
        return !$response->isCached;
    }

    public static function getPriority(): int
    {
        return 210;
    }
}