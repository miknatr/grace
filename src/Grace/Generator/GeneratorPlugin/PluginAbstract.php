<?php

namespace Grace\Generator\GeneratorPlugin;

use Grace\Generator\ZendCodeGenerator\PhpDoc;

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
    public function updateGetterDoctype($fieldName, $fieldConfig, $getterObject)
    {
        return $getterObject;
    }
}
