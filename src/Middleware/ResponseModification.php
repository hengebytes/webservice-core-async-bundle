<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Middleware;

use Hengebytes\WebserviceCoreAsyncBundle\Response\AsyncResponse;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class ResponseModification
{
    public function __construct(
        #[AutowireIterator('webservice_core_async.middleware.response', defaultPriorityMethod: 'getPriority')]
        private iterable $modifiers
    ) {
    }

    public function modifyResponse(AsyncResponse $response): void
    {
        /** @var ResponseModificationInterface $modifier */
        foreach ($this->modifiers as $modifier) {
            if ($modifier->supports($response)) {
                $modifier->modify($response);
            }
        }
    }
}
