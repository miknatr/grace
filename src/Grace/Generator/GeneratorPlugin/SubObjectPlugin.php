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
        return <<<PHP
if (!is_object($fieldStr)) {
    $fieldStr = new \\{$fieldConfig['subObject']}($fieldStr);
}
return $fieldStr;
PHP;
    }
    protected function getSetterBody($fieldName, $fieldConfig)
    {
        $fieldStr = '$this->fields[\'' . $fieldName . '\']';
        return <<<PHP
if (is_object(\$$fieldName)) {
    if (\$$fieldName instanceof \Grace\SQLBuilder\SqlValueInterface) {
        $fieldStr = \$$fieldName;
    } else {
        throw new \LogicException('Field object must be instance of SqlValueInterface');
    }
} else {
    $fieldStr = new \\{$fieldConfig['subObject']}(\$$fieldName);
}
return \$this;
PHP;
    }
}