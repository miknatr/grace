<?php

namespace Grace\Generator\GeneratorPlugin;

use Grace\Generator\ZendCodeGenerator\PhpDoc;
use Grace\Generator\ModelsGenerator;

class SubObjectPlugin extends RecordMethodsPluginAbstract
{
    protected $type = 'subObject';
    protected function getGetterBody($fieldName, $fieldConfig)
    {
        $fieldStr = '$this->fields[\'' . $fieldName . '\']';
        return "return is_object($fieldStr) ? $fieldStr : $fieldStr = new \\{$fieldConfig['subObject']}($fieldStr);";
    }
    protected function getSetterBody($fieldName, $fieldConfig)
    {
        return "\$this->fields['$fieldName'] = is_object(\$$fieldName) ? \$$fieldName : new \\{$fieldConfig['subObject']}(\$$fieldName);" .
            "\n" . '$this->markAsChanged();' . "\n" . 'return $this;';
    }
}