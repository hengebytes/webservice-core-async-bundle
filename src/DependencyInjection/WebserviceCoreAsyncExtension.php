<?php

namespace WebserviceCoreAsyncBundle\DependencyInjection;

use WebserviceCoreAsyncBundle\Cache\CacheManager;
use WebserviceCoreAsyncBundle\Logs\MonologLogHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WebserviceCoreAsyncExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->loadBaseConfigFile($container);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->configureCache($container, $config['cache']);
        $this->configureLogs($container, $config['logs']);
    }

    private function configureCache(ContainerBuilder $container, $params): void
    {
        if (!isset($params['enabled']) || !$params['enabled']) {
            return;
        }
        $this->loadCacheConfigFile($container);
        $definition = $container->getDefinition(CacheManager::class);
        if (isset($params['runtime_adapter'])) {
            $definition->setArgument(1, new Reference($params['runtime_adapter']));
        }
        if (isset($params['persistent_adapter'])) {
            $definition->setArgument(2, new Reference($params['persistent_adapter']));
        }
    }

    private function configureLogs(ContainerBuilder $container, $params): void
    {
        if (!isset($params['enabled'], $params['channel']) || !$params['enabled']) {
            return;
        }
        $this->loadLogsConfigFile($container);
        $definition = $container->getDefinition(MonologLogHandler::class);
        $definition->addTag('monolog.logger', ['channel' => $params['channel']]);
    }

    private function loadCacheConfigFile(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('cache.yaml');
    }

    private function loadLogsConfigFile(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('logs.yaml');
    }

    private function loadBaseConfigFile(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

}
