<?php

namespace Grace\Generator\GeneratorPlugin;

use Grace\Generator\ZendCodeGenerator\PhpDoc;
use Grace\Generator\ModelsGenerator;

class FilterPlugin extends RecordMethodsPluginAbstract
{
    protected $type = 'filter';
    protected function getGetterBody($fieldName, $fieldConfig)
    {
        if (!isset($fieldConfig['filter']['when'])) {
            throw new \LogicException("$fieldName config doesn't contain filter-when section");
        }

        $filterOn = $fieldConfig['filter']['when'];

        if ($filterOn == 'onGet' || $filterOn == 'onSetAndGet') {

            $function = $fieldConfig['filter']['function'];

            return "return $function(\$this->fields['$fieldName']);";
        } else {
            return false;
        }
    }
    protected function getSetterBody($fieldName, $fieldConfig)
    {
        if (!isset($fieldConfig['filter']['when'])) {
            throw new \LogicException("$fieldName config doesn't contain filter-when section");
        }

        $filterOn = $fieldConfig['filter']['when'];

        if ($filterOn == 'onSet' || $filterOn == 'onSetAndGet') {

            $function = $fieldConfig['filter']['function'];

            return "\$this->fields['$fieldName'] = $function(\$$fieldName);\n\$this->markAsChanged();\nreturn \$this;";
        } else {
            return false;
        }
    }
}