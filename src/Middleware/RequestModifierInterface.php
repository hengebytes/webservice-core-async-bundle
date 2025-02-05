<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Middleware;

use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('webservice_core_async.middleware.request')]
interface RequestModifierInterface
{
    public function modify(WSRequest $request): void;

    public function supports(WSRequest $request): bool;

    public static function getPriority(): int;
}