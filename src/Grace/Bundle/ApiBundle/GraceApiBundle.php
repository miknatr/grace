<?php

namespace Grace\Bundle\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Grace\Bundle\ApiBundle\DependencyInjection\GraceApiCompilerPass;
use Grace\Bundle\ApiBundle\DependencyInjection\Security\Factory\ApiFactory;

class GraceApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GraceApiCompilerPass());

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ApiFactory());
    }
}