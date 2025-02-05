<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Tagger;

use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

readonly class CacheTagProvider
{
    public function __construct(
        #[TaggedIterator('webservice_core_async.cache.tagger')]
        private iterable $taggers
    ) {
    }

    public function getStoreTags(WSRequest $request, mixed $response = null): array
    {
        $tags = [[]];
        /** @var TaggerInterface $tagger */
        foreach ($this->taggers as $tagger) {
            if ($tagger->supports($request)) {
                $tags[] = $tagger->getStoreTags($request, $response);
            }
        }

        return array_merge(...$tags);
    }

    public function getInvalidateTags(WSRequest $request, mixed $response = null): array
    {
        $tags = [[]];
        /** @var TaggerInterface $tagger */
        foreach ($this->taggers as $tagger) {
            if ($tagger->supports($request)) {
                $tags[] = $tagger->getInvalidateTags($request, $response);
            }
        }

        return array_merge(...$tags);
    }
}