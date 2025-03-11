<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Cache\Middleware\Request;

use Hengebytes\WebserviceCoreAsyncBundle\Middleware\RequestModifierInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Provider\ParamsProviderInterface;
use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;

readonly class CacheTTLRequestModifier implements RequestModifierInterface
{
    public function __construct(private ?ParamsProviderInterface $paramsProvider = null)
    {
    }

    public function modify(WSRequest $request): void
    {
        if ($request->isCacheTTLSet()) {
            return;
        }

        $ttl = $this->paramsProvider->getCacheTTL($request);

        $request->setCacheTTL($ttl);
    }

    public function supports(WSRequest $request): bool
    {
        return true;
    }

    public static function getPriority(): int
    {
        return 0;
    }
}