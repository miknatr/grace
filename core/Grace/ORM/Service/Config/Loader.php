<?php

namespace Grace\ORM\Service\Config;

use Grace\Cache\CacheInterface;
use Grace\ORM\Service\Config\Element\DefaultElement;
use Grace\ORM\Service\Config\Element\MappingElement;
use Grace\ORM\Service\Config\Element\ModelElement;
use Grace\ORM\Service\Config\Element\ParentElement;
use Grace\ORM\Service\Config\Element\PropertyElement;
use Symfony\Component\Yaml\Yaml;

class Loader
{
    protected $cache;
    protected $resource;
    public function __construct($resource, CacheInterface $cache)
    {
        $this->resource = $resource;
        $this->cache    = $cache;
    }

    public function getConfig()
    {
        return $this->cache->get(
            'grace_config',
            null,
            function () {
                return $this->getConfigRaw();
            }
        );
    }

    protected function getConfigRaw()
    {
        $array = $this->loadResource($this->resource);
        $config = new Config;

        foreach ($array['models'] as $modelName => $modelConfig) {
            $properties = array();
            foreach ($modelConfig['properties'] as $propertyNameWithParentId => $propertyConfig) {
                $mapping = new MappingElement($propertyConfig['mapping']);
                if (!$mapping->localPropertyType && !$mapping->relationLocalProperty) {
                    continue;
                }

                $property = new PropertyElement();
                $property->mapping = $mapping;
                if (isset($propertyConfig['validation'])) {
                    $property->validation = $propertyConfig['validation'];
                }
                if (isset($propertyConfig['default'])) {
                    $property->default = new DefaultElement($propertyConfig['default']);
                }

                $properties[$propertyNameWithParentId] = $property;
            }

            if (empty($properties)) {
                continue;
            }

            $model = new ModelElement();
            $model->properties = $properties;

            if (!isset($modelConfig['parents'])) {
                throw new \LogicException('There is no "parents" config in ' . $modelName . ' model');
            }

            foreach ($modelConfig['parents'] as $propertyNameWithParentId => $parentModelName) {
                $parent = new ParentElement();
                $parent->parentModel = $parentModelName;

                $model->parents[$propertyNameWithParentId] = $parent;
            }

            $config->models[$modelName] = $model;
        }

        return $config;
    }

    private function loadResource($resource)
    {
        if (is_dir($resource)) {
            return $this->loadDir($resource);
        }

        // это ресурсы напрямую из конфига - там лишних файлов быть не должно, поэтому если не существует - эксепшин
        return $this->loadFile($resource, false);
    }

    private function loadDir($resource)
    {
        $config = array();

        $dir = rtrim($resource, '\\/') . '/';
        $d = dir($dir);

        while (false !== ($filename = $d->read())) {
            if ($filename == '..' || $filename == '.') {
                ;
            } elseif (is_dir($dir . $filename)) {
                $config = array_merge_recursive($config, $this->loadDir($dir . $filename));
            } else {
                $config = array_merge_recursive($config, $this->loadFile($dir . $filename));
            }
        }

        $d->close();

        return $config;
    }

    private function loadFile($resource, $skipAllowed = true)
    {
        if (!file_exists($resource)) {
            if (!$skipAllowed) {
                throw new \LogicException($resource . ' does not exist');
            }
        }

        $ext = pathinfo($resource, PATHINFO_EXTENSION);
        if ($ext == 'php') {
            return $this->loadPhp($resource);
        }
        if ($ext == 'yml' || $ext == 'yaml') {
            return $this->loadYml($resource);
        }

        if (!$skipAllowed) {
            throw new \LogicException($resource . ' cannot be loaded');
        }
        return array();
    }

    private function loadYml($resource)
    {
        $config = Yaml::parse($resource);
        if (isset($config['global_config']) && $config['global_config']) {
            unset($config['global_config']);
            return $config;
        }

        $modelName = pathinfo($resource, PATHINFO_FILENAME);
        return array(
            'models' => array($modelName => $config),
        );
    }

    private function loadPhp($resource)
    {
        /** @noinspection PhpIncludeInspection */
        return include $resource;
    }
}
