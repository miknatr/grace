<?php

namespace Grace\Bundle\CommonBundle\Generator\GeneratorPlugin;

use Grace\Bundle\CommonBundle\Generator\ZendCodeGenerator\PhpDoc;

class ParentsPlugin extends PluginAbstract
{
    const CONFIG_PARENTS = 'parents';

    public function getAbstractRecordMethods($modelName, $modelConfig, $recordNamespace, $parent)
    {
        $recordAbstractMethods = array();
        if (isset($modelConfig[self::CONFIG_PARENTS])) {
            $fields = $modelConfig[self::CONFIG_PARENTS];

            foreach ($fields as $fieldName => $parentTable) {

                $docblock = new PhpDoc;
                $docblock->setTags(array(
                                        array(
                                            'name'        => 'return',
                                            'description' => '\\' . $recordNamespace . '\\' . $parentTable . '',
                                        ),
                                   ));

                $getterName = 'get' . ucfirst(substr($fieldName, 0, -strlen('Id')));

                $method = new \Zend_CodeGenerator_Php_Method;
                $method
                    ->setDocblock($docblock)
                    ->setName($getterName)
                    ->setBody('return $this->getOrm()->get' . $parentTable . 'Finder()->getByIdOrFalse($this->get' .
                                  ucfirst($fieldName) . '());');
                $recordAbstractMethods[] = $method;
            }
        }
        return $recordAbstractMethods;
    }
}