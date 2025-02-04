<?php

namespace WebserviceCoreAsyncBundle\Middleware\Request;

use hengebytes\SettingBundle\Interfaces\SettingHandlerInterface;
use WebserviceCoreAsyncBundle\Middleware\RequestModifierInterface;
use WebserviceCoreAsyncBundle\Request\WSRequest;

readonly class BaseUrlRequestModifier implements RequestModifierInterface
{
    public function __construct(private SettingHandlerInterface $settingHandler)
    {
    }

    public function modify(WSRequest $request): void
    {
        if ($request->isBaseUriSet()) {
            return;
        }
        $uri = null;
        if ($request->subService) {
            $uri = $this->settingHandler->get($request->webService . '/' . $request->subService . '/base_url');
        }

        $uri ??= $this->settingHandler->get($request->webService . '/base_url');

        if ($uri === null) {
            throw new \RuntimeException(
                'Base URL not found for ' . $request->webService
                . ($request->subService ? '/' . $request->subService : '')
            );
        }

        $request->setBaseUri($uri);
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