<?php

namespace Grace\Bundle\CommonBundle\Generator\GeneratorPlugin;

use Grace\Bundle\CommonBundle\Generator\ZendCodeGenerator\PhpDoc;
use Grace\Bundle\CommonBundle\Generator\ModelsGenerator;

abstract class RecordMethodsPluginAbstract extends PluginAbstract
{
    protected $type = '';
    abstract protected function getGetterBody($fieldName, $fieldConfig);
    abstract protected function getSetterBody($fieldName, $fieldConfig);
    public function getAbstractRecordMethods($modelName, $modelConfig, $recordNamespace, $parent)
    {
        $recordAbstractMethods = array();

        $fields = $modelConfig[ModelsGenerator::CONFIG_PROPERTIES];

        foreach ($fields as $fieldName => $fieldConfig) {
            if (isset($fieldConfig[$this->type])) {

                $getterBody = $this->getGetterBody($fieldName, $fieldConfig);
                if ($getterBody) {
                    $method = new \Zend_CodeGenerator_Php_Method;
                    $method
                        ->setName('get' . ucfirst($fieldName))
                        ->setBody($getterBody);
                    $recordAbstractMethods[] = $method;
                }


                $setterBody = $this->getSetterBody($fieldName, $fieldConfig);
                if ($setterBody) {
                    $parameter = new \Zend_CodeGenerator_Php_Parameter;
                    $parameter->setName($fieldName);
                    $method = new \Zend_CodeGenerator_Php_Method;

                    $method
                        ->setName('set' . ucfirst($fieldName))
                        ->setParameter($parameter)
                        ->setBody($setterBody);
                    $recordAbstractMethods[] = $method;
                }
            }
        }

        return $recordAbstractMethods;
    }
}