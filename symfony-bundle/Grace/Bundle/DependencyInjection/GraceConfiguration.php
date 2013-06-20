<?php

namespace Grace\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class GraceConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return (new TreeBuilder())
            ->root('grace')
                ->children()
                    ->scalarNode('grace_class')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('namespace_prefix')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('cache_enabled')->isRequired()->end()
                    ->scalarNode('cache_namespace')->isRequired()->end()
                    ->scalarNode('class_directory')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('model_config_resources')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('model_config_fakes')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end()
        ;
    }
}
