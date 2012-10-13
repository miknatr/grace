<?php

namespace Grace\Bundle\CommonBundle\Generator\GeneratorPlugin;

use Grace\Bundle\CommonBundle\Generator\ZendCodeGenerator\PhpDoc;

abstract class PluginAbstract
{
    public function prepareModelConfig($modelName, $modelConfig)
    {
        return $modelConfig;
    }
    public function getAbstractRecordProperties($modelName, $modelConfig, $recordNamespace, $parent)
    {
        return array();
    }
    public function getAbstractRecordMethods($modelName, $modelConfig, $recordNamespace, $parent)
    {
        return array();
    }
    public function getAbstractCollectionProperties($modelName, $modelConfig, $recordNamespace)
    {
        return array();
    }
    public function getAbstractCollectionMethods($modelName, $modelConfig, $recordNamespace)
    {
        return array();
    }
    public function updateGetterDoctype($fieldName, $fieldConfig, $getterObject)
    {
        return $getterObject;
    }
}