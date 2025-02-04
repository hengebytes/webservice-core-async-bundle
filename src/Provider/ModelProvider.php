<?php

namespace WebserviceCoreAsyncBundle\Provider;

use WebserviceCoreAsyncBundle\Exception\NotSupportedModelException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * @template T
 */
readonly class ModelProvider
{
    public function __construct(
        #[TaggedIterator('webservice_core_async.provider.model', defaultPriorityMethod: 'getPriority')]
        private iterable $providers
    ) {
    }

    /**
     * @param class-string<T> $className
     * @return T
     * @throws NotSupportedModelException
     */
    public function getModel(string $className, mixed $data)
    {
        $supportedProvider = null;
        /** @var ModelProviderInterface $provider */
        foreach ($this->providers as $provider) {
            if ($provider->supports($className)) {
                $supportedProvider = $provider;
                break;
            }
        }

        if (!$supportedProvider) {
            throw new NotSupportedModelException($className);
        }

        return $supportedProvider->getModel($data, $this);
    }
}