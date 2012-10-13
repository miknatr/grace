<?php

namespace Grace\Bundle\CommonBundle\Generator\GeneratorPlugin;

use Grace\Bundle\CommonBundle\Generator\ZendCodeGenerator\PhpDoc;
use Grace\Bundle\CommonBundle\Generator\ModelsGenerator;

class CallSubObjectGetterPlugin extends RecordMethodsPluginAbstract
{
    protected $type = 'callSubObjectGetter';
    protected function getGetterBody($fieldName, $fieldConfig)
    {
        list($parentIdField, $parentField) = explode('>', $fieldConfig['callSubObjectGetter']);
        $parentGetter = 'get' . ucfirst(substr($parentIdField, 0, -2)); //подрезали 'Id' в конце названия поля
        $fieldGetter  = 'get' . ucfirst($parentField);

        return "return is_object(\$this->$parentGetter()) ? \$this->$parentGetter()->$fieldGetter() : null;";
    }
    protected function getSetterBody($fieldName, $fieldConfig)
    {
        return 'return $this;';
    }
}