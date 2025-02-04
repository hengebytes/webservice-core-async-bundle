<?php

namespace WebserviceCoreAsyncBundle\Middleware\Request;

use hengebytes\SettingBundle\Interfaces\SettingHandlerInterface;
use WebserviceCoreAsyncBundle\Middleware\RequestModifierInterface;
use WebserviceCoreAsyncBundle\Request\WSRequest;

readonly class TimeoutRequestModifier implements RequestModifierInterface
{
    public function __construct(private SettingHandlerInterface $settingHandler)
    {
    }

    public function modify(WSRequest $request): void
    {
        if ($request->subService) {
            $timeout = $this->settingHandler->get(
                'timeout/' . $request->webService . '/' . $request->subService . '/' . $request->getCustomAction()
            );
        }

        $timeout ??= $this->settingHandler->get(
            'timeout/' . $request->webService . '/' . $request->getCustomAction()
        );

        $timeout = (float)$timeout;

        if ($timeout > 0) {
            $request->setTimeout($timeout);
        }
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