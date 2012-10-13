<?php

namespace Grace\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class GraceApiCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('grace_simple_api.security.authentication.listener');

        $taggedServices = $container->findTaggedServiceIds('grace_api.user_finder');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addUserFinder', array(new Reference($id)));
        }
    }
}