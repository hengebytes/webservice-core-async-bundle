<?php

namespace Hengebytes\WebserviceCoreAsyncBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('hb_webservice_core_async');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('params_provider')
                    ->info('The service id of the params provider to use. Must implement ParamsProviderInterface. Available options: symfony_params, settings_bundle, some_custom_service_name')
                    ->defaultNull()
                ->end()
                ->arrayNode('cache')
                ->canBeEnabled()
                    ->children()
                        ->scalarNode('persistent_adapter')->end()
                        ->scalarNode('runtime_adapter')->end()
                    ->end()
                ->end()
                ->arrayNode('logs')
                ->canBeEnabled()
                    ->children()
                        ->scalarNode('channel')->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}