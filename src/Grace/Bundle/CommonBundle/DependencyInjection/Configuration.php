<?php

namespace Grace\Bundle\CommonBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $children    = $treeBuilder->root('grace')->children();


        $namespace = $children->arrayNode('namespace');
        $namespace->isRequired()->cannotBeEmpty();
        $namespace->children()->scalarNode('common_prefix_without_slash')->isRequired()->cannotBeEmpty();
        $namespace->children()->scalarNode('record')->defaultValue('Model');
        $namespace->children()->scalarNode('finder')->defaultValue('Finder');
        $namespace->children()->scalarNode('mapper')->defaultValue('Mapper');
        $namespace->children()->scalarNode('manager_class')->defaultValue('ORMManager')->beforeNormalization();

        $cache = $children->arrayNode('cache');
        $cache->isRequired()->cannotBeEmpty();
        $cache->children()->scalarNode('enabled')->isRequired();
        $cache->children()->scalarNode('namespace')->isRequired();


        $children->variableNode('model_config_resources')->isRequired();

        $children->scalarNode('record_observer_class')->isRequired()->cannotBeEmpty();
        $children->scalarNode('php_storage_file')->isRequired()->cannotBeEmpty();
        $children->scalarNode('real_class_directory')->isRequired()->cannotBeEmpty();
        $children->scalarNode('abstract_class_directory')->isRequired()->cannotBeEmpty();

        $children->variableNode('generator_plugins')->defaultValue(array());
        $children->variableNode('form_type_translations')->defaultValue(array());


        return $treeBuilder;
    }
}
