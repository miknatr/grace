<?php

namespace Grace\Bundle\DependencyInjection;

use Grace\Bundle\DependencyInjection\GraceConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GraceExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->loadConfiguration($configs);
        $this->exportConfigurationToContaner($container, $config);
        $this->loadServices($container);
    }
    private function loadConfiguration(array $configs)
    {
        $configuration = new GraceConfiguration();
        $config        = $this->processConfiguration($configuration, $configs);

        return $config;
    }
    private function exportConfigurationToContaner(ContainerBuilder $container, $config)
    {
        foreach ($config as $k => $v) {
            $container->setParameter('grace.' . $k, $v);
        }
    }
    private function loadServices(ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
