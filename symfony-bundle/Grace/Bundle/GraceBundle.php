<?php

namespace Grace\Bundle;

use Grace\Bundle\DependencyInjection\Compiler\AddGraceValidatorMetadataLoaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GraceBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AddGraceValidatorMetadataLoaderPass());
    }
}
