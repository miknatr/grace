<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Grace\ORM\Service;

use Grace\ORM\Service\Config\Element\ModelElement;
use Grace\ORM\Service\Config\Config;
use Grace\ORM\Service\ClassNameProvider;
use Symfony\Component\Yaml\Yaml;

class Generator
{
    private $modelsConfig;
    private $classNameProvider;
    private $baseDir;

    const GENCODE_MARKER = '    /* BEGIN GRACE GENERATED CODE */';

    public function __construct(Config $modelsConfig, ClassNameProvider $classNameProvider, $baseDir)
    {
        $this->modelsConfig      = $modelsConfig;
        $this->classNameProvider = $classNameProvider;
        $this->baseDir           = rtrim($baseDir, '\\/');
    }
    public function generate($dryRun = false)
    {
        $newFilesContent = array();

        foreach ($this->modelsConfig->models as $name => $config) {
            $fileName = $this->baseDir . str_replace('\\', '/', $this->classNameProvider->getModelClass($name)) . '.php';
            $newFilesContent[$fileName] = self::addGeneratedMethodsToModelContent(file_get_contents($fileName), $config);
        }

        //we need second cycle, we don't write into file on error in first cycle
        foreach ($newFilesContent as $fileName => $fileContent) {
            if (is_writable($fileName)) {
                if (!$dryRun) {
                    file_put_contents($fileName, $fileContent);
                }
            } else {
                throw new \LogicException($fileName . ' is not writable');
            }
        }
    }
    private static function addGeneratedMethodsToModelContent($content, ModelElement $config)
    {
        $content = trim($content);
        $lines = explode("\n", $content);

        if ($lines[count($lines) - 1] != '}') {
            throw new \LogicException('Invalid model file');
        }

        $beginNum = array_search(self::GENCODE_MARKER, $lines);
        if ($beginNum === false) {
            unset($lines[count($lines) - 1]);
        } else {
            array_splice($lines, $beginNum);
        }

        $beginPos        = strpos($content, self::GENCODE_MARKER);
        $handWrittenCode = $beginPos !== false ? substr($content, 0, $beginPos) : $content;

        return rtrim(implode("\n", $lines)) . "\n\n\n" . self::GENCODE_MARKER . "\n" . self::generateModelMethodsCode($config, $handWrittenCode) . "\n}\n";
    }
    private static function generateModelMethodsCode(ModelElement $config, $handWrittenCode)
    {
        $r = '';

        foreach ($config->properties as $propertyWithId => $propertyConfig) {
            //TODO magic string id
            if ($propertyWithId != 'id') {
                $getterName = strpos($handWrittenCode, 'function get' . ucfirst($propertyWithId) . '(') !== false ? 'get' . ucfirst($propertyWithId) . 'Generated' : 'get' . ucfirst($propertyWithId);

                $r .= <<<PHP

    public function $getterName()
    {
        return \$this->properties['$propertyWithId'];
    }
PHP;

                if ($propertyConfig->mapping->localPropertyType) {
                    $setterName = strpos($handWrittenCode, 'function set' . ucfirst($propertyWithId) . '(') !== false ? 'set' . ucfirst($propertyWithId) . 'Generated' : 'set' . ucfirst($propertyWithId);

                    $r .= <<<PHP

    public function $setterName(\$$propertyWithId)
    {
        \$this->properties['$propertyWithId'] = \$this->orm->typeConverter->convertOnSetter('{$propertyConfig->mapping->localPropertyType}', \$$propertyWithId);
        \$this->markAsChanged();
        return \$this;
    }
PHP;
                }
            }
        }

        foreach ($config->parents as $propertyName => $parentConfig) {
            //TODO соглашение, что поле должно заканчиваться на Id иначе ничего не работает
            $parentGetterName = 'get' . ucfirst(substr($propertyName, 0, -strlen('Id')));
            $getterName = 'get' . ucfirst($propertyName);
            $r .= <<<PHP

    public function $parentGetterName()
    {
        return \$this->orm->getFinder('$parentConfig->parentModel')->getByIdOrFalse(\$this->$getterName());
    }
PHP;
        }

        return $r;
    }
}
