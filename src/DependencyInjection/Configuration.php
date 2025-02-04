<?php

namespace WebserviceCoreAsyncBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('webservice_core_async');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
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