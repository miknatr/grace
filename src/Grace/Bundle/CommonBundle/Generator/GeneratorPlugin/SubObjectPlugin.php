<?php

namespace Grace\Bundle\CommonBundle\Generator\GeneratorPlugin;

use Grace\Bundle\CommonBundle\Generator\ZendCodeGenerator\PhpDoc;
use Grace\Bundle\CommonBundle\Generator\ModelsGenerator;

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