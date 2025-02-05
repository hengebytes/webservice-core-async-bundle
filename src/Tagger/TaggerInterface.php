<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Tagger;

use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('webservice_core_async.cache.tagger')]
interface TaggerInterface
{
    public function getStoreTags(WSRequest $request, mixed $response = null): array;

    public function getInvalidateTags(WSRequest $request, mixed $response = null): array;

    public function supports(WSRequest $request): bool;
}
