<?php

namespace Grace\Generator\GeneratorPlugin;

use Grace\Generator\ZendCodeGenerator\PhpDoc;
use Grace\Generator\ModelsGenerator;

class FormDataPlugin extends PluginAbstract
{
    public function updateGetterDoctype($fieldName, $fieldConfig, $getterObject)
    {
        if (isset($fieldConfig['form'])) {
            $formData = array();
            foreach ($fieldConfig['form'] as $attr => $value) {
                if (is_array($value)) {
                    $vals = array();
                    foreach ($value as $k => $v) {
                        $vals[] = "\"$k\"=\"$v\"";
                    }
                    $formData[] = $attr . '={' . implode(', ', $vals) . '}';
                } else {
                    $formData[] = $attr . '="' . $value . '"';
                }
            }
            $formDataString = implode(', ', $formData);

            $docblock = new PhpDoc;
            $docblock->setTags(array(
                                    array(
                                        'name'        => 'FormData(' . $formDataString . ')',
                                        'description' => ''
                                    ),
                               ));

            $getterObject->setDocblock($docblock);
        }

        return $getterObject;
    }
}