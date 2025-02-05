<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Callback;

use Hengebytes\WebserviceCoreAsyncBundle\Response\ParsedResponse;

readonly class OnResponseReceivedCallback
{
    public function __construct(private \Closure $callback)
    {
    }

    public function __invoke(
       ParsedResponse $callbackRequest,
    ): void {
        ($this->callback)($callbackRequest);
    }

}