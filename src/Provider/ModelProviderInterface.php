<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\Provider;

use Hengebytes\WebserviceCoreAsyncBundle\Request\WSRequest;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('webservice_core_async.provider.model')]
interface ModelProviderInterface
{
    public function getModel(mixed $data, ModelProvider $modelProvider, ?WSRequest $WSRequest = null): object;

    public function supports(string $model): bool;

    public static function getPriority(): int;

}