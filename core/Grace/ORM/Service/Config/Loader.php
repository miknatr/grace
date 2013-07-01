<?php

namespace Grace\ORM\Service\Config;

use Grace\Cache\CacheInterface;
use Grace\ORM\Service\Config\Element\DefaultElement;
use Grace\ORM\Service\Config\Element\MappingElement;
use Grace\ORM\Service\Config\Element\ModelElement;
use Grace\ORM\Service\Config\Element\PropertyElement;
use Grace\ORM\Service\Config\Element\ProxyElement;
use Symfony\Component\Yaml\Yaml;

class Loader
{
    protected $cache;
    protected $resource;
    public function __construct($resource, CacheInterface $cache = null)
    {
        $this->resource = $resource;
        $this->cache    = $cache;
    }

    public function getConfig()
    {
        if (!$this->cache) {
            return $this->getConfigRaw();
        }
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
            foreach ($modelConfig['properties'] as $propertyName => $propertyConfig) {
                if (!isset($propertyConfig['mapping'])) {
                    continue;
                }
                $mapping = $this->parseMapping($propertyConfig['mapping']);

                if ($propertyName == 'id' && !$mapping->localPropertyType) {
                    throw new \LogicException("Bad mapping: {$modelName}.{$propertyName} must have a local mapping");
                }

                $property = new PropertyElement();
                if (isset($propertyConfig['validation'])) {
                    $property->validation = $propertyConfig['validation'];
                }
                if (isset($propertyConfig['default'])) {
                    $property->default = new DefaultElement($propertyConfig['default']);
                }

                $property->isSettable = $mapping->localPropertyType || $mapping->foreignKeyTable;
                $property->isLocalInDb = $property->isSettable; // in theory isSettable can be different from isLocalInDb

                $property->type = $this->getPropertyType($array, $modelName, $propertyName);
                // we only allow NULL in foreign key properties and proxy-properties (which are null when the foreign key is null)
                $property->isNullable = $mapping->foreignKeyTable || $mapping->relationLocalProperty;

                $property->resolvesToModelName = $mapping->foreignKeyTable;
                $property->dependentProxies = $this->getDependentProxies($array, $modelName, $propertyName);

                $property->proxy = $this->parseProxy($array, $modelName, $propertyName);

                if ($property->type === null) {
                    throw new \LogicException("Cannot parse type for {$modelName}.{$propertyName}");
                }

                $properties[$propertyName] = $property;
            }

            if (empty($properties)) {
                continue;
            }

            $model = new ModelElement();
            $model->properties = $properties;

            if (isset($modelConfig['parents'])) {
                throw new \LogicException('There is unsupported "parents" config in ' . $modelName . ' model');
            }

            $config->models[$modelName] = $model;
        }

        return $config;
    }

    private function parseMapping($mapping)
    {
        $me = new MappingElement();
        if (preg_match('/^(\w+):(\w+)$/', $mapping, $match)) {
            $me->relationLocalProperty = $match[1];
            $me->relationForeignProperty = $match[2];
        } elseif ($mapping[0] == '^') {
            $me->foreignKeyTable = substr($mapping, 1);
        } elseif ($mapping) {
            $me->localPropertyType = $mapping;
        } else {
            throw new \Exception('bad config: cannot parse mapping');
        }
        return $me;
    }

    private function getPropertyType($rawConfig, $modelName, $propName)
    {
        if (empty($rawConfig['models'][$modelName]['properties'][$propName]['mapping'])) {
            return null;
        }
        $mapping = $this->parseMapping($rawConfig['models'][$modelName]['properties'][$propName]['mapping']);

        // local field
        if ($mapping->localPropertyType) {
            return $mapping->localPropertyType;
        }

        // foreign key
        if ($mapping->foreignKeyTable) {
            // we need the type of ID of the foreign table
            return $this->getPropertyType($rawConfig, $mapping->foreignKeyTable, 'id');
        }

        // proxy field
        if ($mapping->relationLocalProperty) {
            if (empty($rawConfig['models'][$modelName]['properties'][$mapping->relationLocalProperty]['mapping'])) {
                return null;
            }
            $localPropMapping = $this->parseMapping($rawConfig['models'][$modelName]['properties'][$mapping->relationLocalProperty]['mapping']);
            $foreignModelName = $localPropMapping->foreignKeyTable;
            if (!$foreignModelName) {
                throw new \Exception("Bad config, very bad");
            }

            return $this->getPropertyType($rawConfig, $foreignModelName, $mapping->relationForeignProperty);
        }

        return null;
    }

    private function getDependentProxies($rawConfig, $modelName, $propName)
    {
        if (empty($rawConfig['models'][$modelName]['properties'][$propName]['mapping'])) {
            return null;
        }
        $mapping = $this->parseMapping($rawConfig['models'][$modelName]['properties'][$propName]['mapping']);

        if (!$mapping->foreignKeyTable) {
            return array();
        }

        $list = array();
        foreach ($rawConfig['models'][$modelName]['properties'] as $iPropName => $rawPropConfig) {
            $proxy = $this->parseProxy($rawConfig, $modelName, $iPropName);
            if ($proxy && $proxy->localField == $propName) {
                $list[$iPropName] = $proxy;
            }
        }
        return $list;
    }

    private function parseProxy($rawConfig, $modelName, $propName)
    {
        if (empty($rawConfig['models'][$modelName]['properties'][$propName]['mapping'])) {
            return null;
        }
        $mapping = $this->parseMapping($rawConfig['models'][$modelName]['properties'][$propName]['mapping']);

        if (!$mapping->relationLocalProperty) {
            return null;
        }

        if (empty($rawConfig['models'][$modelName]['properties'][$mapping->relationLocalProperty]['mapping'])) {
            throw new \LogicException("Bad mapping in {$modelName}.{$propName}: depends on {$modelName}.{$mapping->relationLocalProperty} which has no mapping");
        }
        $relationMapping = $this->parseMapping($rawConfig['models'][$modelName]['properties'][$mapping->relationLocalProperty]['mapping']);
        if (!$relationMapping->foreignKeyTable) {
            throw new \LogicException("Bad mapping in {$modelName}.{$propName}: we need {$modelName}.{$mapping->relationLocalProperty} to be a foreign key");
        }

        $proxy = new ProxyElement();
        $proxy->localField   = $mapping->relationLocalProperty;
        $proxy->foreignTable = $relationMapping->foreignKeyTable;
        $proxy->foreignField = $mapping->relationForeignProperty;
        return $proxy;
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
