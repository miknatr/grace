<?php

namespace Grace\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/*
 * Нужно добавить в Symfony/Bundle/FrameworkBundle/Resources/config/validator.xml свой Mapping\Loader.
 *
 * Существующие лоадеры не подходят:
 *
 * YamlFilesLoader, XmlFilesLoader - нужно генерить эти конфиги вместо использования конфига моделей,
 * можно генерить эти конфиги из орм-конфига, но это лишние файлы и их поддержка,
 * лишние изменения в пул-реквесте.
 *
 * StaticMethodLoader - можно прописать метадату у каждой модели в статическом методе, но мы хотим при этом
 * пользоваться сервисами (лоадером кофига, кэшем и т.д.), поэтому статика здесь не катит.
 *
 * AnnotationLoader - можно прописать у каждого геттера аннотацию, но если хотим уходить от генерируемых
 * геттеров в пользу магии и плагина к IDE, то вариант тоже не катит.
 *
 * Поэтому делаем свой лоадер и добавляем его в validator.mapping.loader.loader_chain.
 * Для этого добавляем в первый аргумент (массив лоадеров) в DI-контейнере у сервиса
 * validator.mapping.loader.loader_chain наш лоадер.
 */
class AddGraceValidatorMetadataLoaderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('validator')) {
            return;
        }

        if (!$container->hasDefinition('validator.mapping.loader.loader_chain')) {
            return;
        }

        $loaders = $container->getDefinition('validator.mapping.loader.loader_chain')->getArgument(0);
        $loaders[] = new Reference('');
        $container->getDefinition('validator.mapping.loader.loader_chain')->replaceArgument(0, $loaders);
    }
}
