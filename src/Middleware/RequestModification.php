<?php

namespace WebserviceCoreAsyncBundle\Middleware;

use WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

readonly class RequestModification
{
    public function __construct(
        #[TaggedIterator('webservice_core_async.middleware.request', defaultPriorityMethod: 'getPriority')]
        private iterable $modifiers
    ) {
    }

    public function modifyRequest(WSRequest $request): void
    {
        /** @var RequestModifierInterface $modifier */
        foreach ($this->modifiers as $modifier) {
            if ($modifier->supports($request)) {
                $modifier->modify($request);
            }
        }
    }
}