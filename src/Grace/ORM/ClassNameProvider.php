<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM;

/**
 * @inheritdoc
 */
class ClassNameProvider implements ClassNameProviderInterface
{
    protected $commonNamespace;
    protected $modelNamespace = 'Model';
    protected $modelPrefix = '';
    protected $modelPostfix = '';
    protected $finderNamespace = 'Finder';
    protected $finderPrefix = '';
    protected $finderPostfix = 'Finder';
    protected $mapperNamespace = 'Mapper';
    protected $mapperPrefix = '';
    protected $mapperPostfix = 'Mapper';

    /**
     * @param string $commonNamespace common namespace prefix
     */
    public function __construct($commonNamespace = '')
    {
        $this->commonNamespace = $commonNamespace;
    }
    /**
     * @inheritdoc
     */
    public function getBaseClass($modelClass)
    {
        $type         = 'model';
        $namespaceLen = 0;
        if ($this->commonNamespace != '') {
            $namespaceLen += strlen($this->commonNamespace) + 1;
        }
        if ($this->{$type . 'Namespace'} != '') {
            $namespaceLen += strlen($this->{$type . 'Namespace'}) + 1;
        }
        $baseClass = trim($modelClass, '\\');
        $baseClass = substr($baseClass, $namespaceLen);
        if (strlen($this->{$type . 'Prefix'}) > 0) {
            $baseClass = substr($baseClass, strlen($this->{$type . 'Prefix'}));
        }
        if (strlen($this->{$type . 'Postfix'}) > 0) {
            $baseClass = substr($baseClass, 0, -strlen($this->{$type . 'Postfix'}));
        }
        return $baseClass;
    }
    /**
     * @inheritdoc
     */
    public function getBaseClassFromFinderClass($finderClass)
    {
        $type         = 'finder';
        $namespaceLen = 0;
        if ($this->commonNamespace != '') {
            $namespaceLen += strlen($this->commonNamespace) + 1;
        }
        if ($this->{$type . 'Namespace'} != '') {
            $namespaceLen += strlen($this->{$type . 'Namespace'}) + 1;
        }
        $baseClass = trim($finderClass, '\\');
        $baseClass = substr($baseClass, $namespaceLen);
        if (strlen($this->{$type . 'Prefix'}) > 0) {
            $baseClass = substr($baseClass, strlen($this->{$type . 'Prefix'}));
        }
        if (strlen($this->{$type . 'Postfix'}) > 0) {
            $baseClass = substr($baseClass, 0, -strlen($this->{$type . 'Postfix'}));
        }
        return $baseClass;
    }
    /**
     * @inheritdoc
     */
    public function getModelClass($baseClass)
    {
        return $this->getClass($baseClass, 'model');
    }
    /**
     * @inheritdoc
     */
    public function getFinderClass($baseClass)
    {
        return $this->getClass($baseClass, 'finder');
    }
    /**
     * @inheritdoc
     */
    public function getMapperClass($baseClass)
    {
        return $this->getClass($baseClass, 'mapper');
    }
    /**
     * Gets full class name
     * @param $baseClass base model class
     * @param $type      FinderSql, mapper, collection, record
     * @return string
     */
    protected function getClass($baseClass, $type)
    {
        return '\\' . ($this->commonNamespace == '' ? '' : $this->commonNamespace . '\\') .
            ($this->{$type . 'Namespace'} == '' ? '' : $this->{$type . 'Namespace'} . '\\') .
            $this->{$type . 'Prefix'} . $baseClass . $this->{$type . 'Postfix'};
    }
}
