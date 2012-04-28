<?php

namespace Grace\ORM;

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
    protected $collectionNamespace = 'Collection';
    protected $collectionPrefix = '';
    protected $collectionPostfix = 'Collection';

    public function __construct($commonNamespace = '') {
        $this->commonNamespace = $commonNamespace;
    }
    protected function getClass($baseClass, $type)
    {
        return '\\' . ($this->commonNamespace == '' ? '' : $this->commonNamespace . '\\')
            . ($this->{$type . 'Namespace'} == '' ? '' : $this->{$type . 'Namespace'} . '\\')
            . $this->{$type . 'Prefix'} . $baseClass . $this->{$type . 'Postfix'};
    }
    public function getBaseClass($modelClass)
    {
        $type = 'model';
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
            $baseClass = substr($modelClass, strlen($this->{$type . 'Prefix'}));
        }
        if (strlen($this->{$type . 'Postfix'}) > 0) {
            $baseClass = substr($modelClass, 0, -$this->{$type . 'Postfix'});
        }
        return $baseClass;
    }
    public function getModelClass($baseClass)
    {
        return $this->getClass($baseClass, 'model');
    }
    public function getFinderClass($baseClass)
    {
        return $this->getClass($baseClass, 'finder');
    }
    public function getMapperClass($baseClass)
    {
        return $this->getClass($baseClass, 'mapper');
    }
    public function getCollectionClass($baseClass)
    {
        return $this->getClass($baseClass, 'collection');
    }
}