<?php

namespace Grace\Bundle\CommonBundle\Generator;

use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    private $output = false;
    public function load(array $resources, $output = false)
    {
        $config = array();

        foreach ($resources as $resource) {
            $config = array_merge_recursive($config, $this->loadResource($resource));
        }

        return $config;
    }
    private function output($msg)
    {
        if ($this->output) {
            echo $msg . "\n";
        }
    }
    private function loadResource($resource)
    {
        if (is_dir($resource)) {
            return $this->loadDir($resource);
        } else {
            //это ресурсы напрямую из конфига - там лишних файлов быть не должно, поэтому если не существует - эксепшин
            return $this->loadFile($resource, false);
        }
    }
    private function loadDir($resource)
    {
        $this->output('Loading dir: ' . $resource);

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
        $this->output('Loading file: ' . $resource);

        if (!file_exists($resource)) {
            if (!$skipAllowed) {
                throw new \LogicException($resource . ' is not exist');
            }
        }

        $ext = pathinfo($resource, PATHINFO_EXTENSION);
        if ($ext == 'php') {
            return $this->loadPhp($resource);
        } elseif ($ext == 'yml' || $ext == 'yaml') {
            return $this->loadYml($resource);
        } else {
            if (!$skipAllowed) {
                throw new \LogicException($resource . ' can not be loaded');
            }
            return array();
        }
    }
    private function loadYml($resource)
    {
        $config = Yaml::parse($resource);
        if (isset($config['global_config']) && $config['global_config']) {
            unset($config['global_config']);
            return $config;
        } else {
            $modelName = pathinfo($resource, PATHINFO_FILENAME);
            return array('models' => array($modelName => $config));
        }
    }
    private function loadPhp($resource)
    {
        return array();
    }
}