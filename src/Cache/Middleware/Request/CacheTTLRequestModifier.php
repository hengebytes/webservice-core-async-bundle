<?php

namespace WebserviceCoreAsyncBundle\Cache\Middleware\Request;

use hengebytes\SettingBundle\Interfaces\SettingHandlerInterface;
use WebserviceCoreAsyncBundle\Middleware\RequestModifierInterface;
use WebserviceCoreAsyncBundle\Request\WSRequest;

readonly class CacheTTLRequestModifier implements RequestModifierInterface
{
    public function __construct(private SettingHandlerInterface $settingHandler)
    {
    }

    public function modify(WSRequest $request): void
    {
        if ($request->isCacheTTLSet()) {
            return;
        }
        if ($request->subService) {
            $ttl = (int)$this->settingHandler->get(
                'cache/' . $request->webService . '/' . $request->subService . '/' . $request->getCustomAction() . '/ttl', '0'
            );
        }
        $ttl ??= (int)$this->settingHandler->get(
            'cache/' . $request->webService . '/' . $request->getCustomAction() . '/ttl', '0'
        );

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