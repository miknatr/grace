<?php

namespace Grace\Bundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Grace\Bundle\DataCollector\QueryCollector;
use Grace\Bundle\DependencyInjection\GraceExtension;
use Grace\Bundle\DispatchedModelObserver;
use Grace\Bundle\GracePlusSymfony;
use Grace\Bundle\Request\ParamConverter\GraceParamConverter;
use Grace\Bundle\Validator\UniqueValidator;
use Grace\Cache\CacheInterface;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\Config\Config;
use Grace\ORM\Service\IdentityMap;
use Grace\ORM\Service\TypeConverter;
use Grace\ORM\Service\UnitOfWork;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class ServicesTest extends \PHPUnit_Framework_TestCase
{
    protected function createTestContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            //'kernel.root_dir'  => __DIR__.'/'
            'kernel.debug'       => true,
            'kernel.bundles'     => array(),
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'grace_db'           => array(
                'adapter'  => 'mysqli',
                'host'     => TEST_MYSQLI_HOST,
                'port'     => TEST_MYSQLI_PORT,
                'database' => TEST_MYSQLI_DATABASE,
                'user'     => TEST_MYSQLI_NAME,
                'password' => TEST_MYSQLI_PASSWORD,
            ),
        )));
        $container->set('annotation_reader', new AnnotationReader());
        $container->set('event_dispatcher', new EventDispatcher());
        $container->set('logger', new Logger('test'));
        $container->set('security.role_hierarchy', new RoleHierarchy(array()));

        $loader = new GraceExtension();
        $container->registerExtension($loader);

        $loader->load(array(
            'grace' => array(
                'class_directory'        => './',
                'model_config_resources' => __DIR__.'/Resources/models/TaxiPassenger.yml',
                'model_config_common'    => __DIR__.'/Resources/models.yml',
                'namespace_prefix'       => 'Grace\Bundle\Tests\Plug',
                'cache_enabled'          => false,
                'cache_namespace'        => '',
            )
        ), $container);

        $container->compile();

        return $container;
    }

    public function testServices()
    {
        $container = $this->createTestContainer();

        /** @var $orm GracePlusSymfony */
        $orm = $container->get('grace_orm');

        $this->assertTrue($orm instanceof GracePlusSymfony);
        $this->assertTrue($orm->eventDispatcher instanceof EventDispatcher);
        $this->assertTrue($orm->logger instanceof Logger);
        $this->assertTrue($orm->db instanceof ConnectionInterface);
        $this->assertTrue($orm->classNameProvider instanceof ClassNameProvider);
        $this->assertTrue($orm->modelObserver instanceof DispatchedModelObserver);
        $this->assertTrue($orm->typeConverter instanceof TypeConverter);
        $this->assertTrue($orm->identityMap instanceof IdentityMap);
        $this->assertTrue($orm->unitOfWork instanceof UnitOfWork);
        $this->assertTrue($orm->roleHierarchy instanceof RoleHierarchyInterface);

        $this->assertTrue($orm->config instanceof Config);
        $this->assertEquals($orm->config->models['TaxiPassenger']->properties['name']->mapping, 'string');

        $this->assertTrue($container->get('grace_db') instanceof ConnectionInterface);
        $this->assertTrue($container->get('cache') instanceof CacheInterface);
        $this->assertTrue($container->get('grace_param_converter') instanceof GraceParamConverter);
        $this->assertTrue($container->get('grace_validator_unique') instanceof UniqueValidator);
        $this->assertTrue($container->get('grace_query_collector') instanceof QueryCollector);
    }
}
