<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Logs\Middleware;

use Hengebytes\WebserviceCoreAsyncBundle\Callback\OnResponseReceivedCallback;
use Hengebytes\WebserviceCoreAsyncBundle\Logs\MonologLogHandler;
use Hengebytes\WebserviceCoreAsyncBundle\Middleware\ResponseModificationInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;

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