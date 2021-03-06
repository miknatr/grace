<?php

namespace Grace\ORM\Service\Config;

use Grace\Cache\CacheInterface;
use Grace\ORM\Service\Config\Element\ModelElement;
use Grace\ORM\Service\Config\Element\PropertyElement;
use Grace\ORM\Service\TypeConverter;
use Symfony\Component\Yaml\Yaml;

class Loader
{
    protected $cache;
    protected $resource;
    /** @var TypeConverter */
    private $typeConverter;

    public function __construct($resource, TypeConverter $typeConverter, CacheInterface $cache = null)
    {
        $this->resource      = $resource;
        $this->typeConverter = $typeConverter;
        $this->cache         = $cache;
    }

    public function getConfig()
    {
        if (!$this->cache) {
            return $this->getConfigRaw();
        }

        return $this->cache->get(
            'grace_config',
            0,
            function () {
                return $this->getConfigRaw();
            }
        );
    }

    protected function getConfigRaw()
    {
        $array  = $this->loadResource($this->resource);
        $config = new Config;

        foreach ($array['models'] as $modelName => $modelConfig) {
            $properties = array();
            foreach ($modelConfig['properties'] as $propertyName => $propertyConfig) {
                if (!isset($propertyConfig['mapping'])) {
                    continue;
                }

                $property = PropertyElement::create(
                    $this->typeConverter,
                    $modelName,
                    $propertyName,
                    $propertyConfig['mapping'],
                    isset($propertyConfig['default']) ? $propertyConfig['default'] : null,
                    isset($propertyConfig['validation']) ? $propertyConfig['validation'] : null
                );

                if (!$property) {
                    continue;
                }
                $properties[$propertyName] = $property;
            }

            if (empty($properties)) {
                continue;
            }

            $model = new ModelElement();
            $model->properties = $properties;

            if (isset($modelConfig['parents'])) {
                throw new ConfigLoadException('Unsupported "parents" config in ' . $modelName . ' model');
            }

            $config->models[$modelName] = $model;
        }

        PropertyElement::resolveConfig($config, $this->typeConverter);

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
        $d   = dir($dir);

        while (false !== ($filename = $d->read())) {
            if ($filename == '..' || $filename == '.') {
                continue;
            }

            if (is_dir($dir . $filename)) {
                $config = array_merge_recursive($config, $this->loadDir($dir . $filename));
            } else {
                $config = array_merge_recursive($config, $this->loadFile($dir . $filename));
            }
        }

        $d->close();

        return $config;
    }

    private function loadFile($resource, $isSkipAllowed = true)
    {
        if (!file_exists($resource)) {
            if (!$isSkipAllowed) {
                throw new ConfigLoadException($resource . ' does not exist');
            }
        }

        $ext = pathinfo($resource, PATHINFO_EXTENSION);
        if ($ext == 'php') {
            return $this->loadPhp($resource);
        }
        if ($ext == 'yml' || $ext == 'yaml') {
            return $this->loadYml($resource);
        }

        if (!$isSkipAllowed) {
            throw new ConfigLoadException($resource . ' cannot be loaded');
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
