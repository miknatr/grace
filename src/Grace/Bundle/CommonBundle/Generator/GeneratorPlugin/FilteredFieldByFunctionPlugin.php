<?php

namespace Grace\Bundle\CommonBundle\Generator\GeneratorPlugin;

use Grace\Bundle\CommonBundle\Generator\ZendCodeGenerator\PhpDoc;
use Grace\Bundle\CommonBundle\Generator\ModelsGenerator;

class FilteredFieldByFunctionPlugin extends RecordMethodsPluginAbstract
{
    protected $type = 'filteredFieldByFunction';
    protected function getGetterBody($fieldName, $fieldConfig)
    {
        list($fieldForFunction, $function) = explode('>', $fieldConfig['filteredFieldByFunction']);
        $fieldStr = '$this->fields[\'' . $fieldForFunction . '\']';
        return "return $function($fieldStr);";
    }
    protected function getSetterBody($fieldName, $fieldConfig)
    {
        return 'return $this;';
    }
}