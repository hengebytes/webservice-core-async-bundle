<?php

namespace WebserviceCoreAsyncBundle\Provider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('webservice_core_async.provider.model')]
interface ModelProviderInterface
{
    public function getModel(mixed $data, ModelProvider $modelProvider): object;

    public function supports(string $model): bool;

    public static function getPriority(): int;

}