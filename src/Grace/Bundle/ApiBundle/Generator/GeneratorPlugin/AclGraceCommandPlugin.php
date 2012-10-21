<?php

namespace Grace\Bundle\ApiBundle\Generator\GeneratorPlugin;

use Grace\Generator\GeneratorPlugin\PluginAbstract;

class AclGraceCommandPlugin extends PluginAbstract
{
    const ACL_RECORD_BASE_CLASS = '\\Grace\\Bundle\\ApiBundle\\Model\\ResourceAbstract';
    const ACL_COLLECTION_BASE_CLASS = '\\Grace\\Bundle\\ApiBundle\\Collection\\CollectionAbstract';

    public function prepareModelConfig($modelName, $modelConfig)
    {
        if (isset($modelConfig['extends']) and $modelConfig['extends'] == 'api_generation') {
            $modelConfig['extends'] = self::ACL_RECORD_BASE_CLASS;
        }
        if (isset($modelConfig['collection_extends']) and $modelConfig['collection_extends'] == 'api_generation') {
            $modelConfig['collection_extends'] = self::ACL_COLLECTION_BASE_CLASS;
        }

        return $modelConfig;
    }

    private function preparePrivileges(array $privileges)
    {
        foreach ($privileges as $privilege => &$cases) {
            foreach ($cases as &$case) {
                $case = static::prepareCase($case);
            }
        }
        return $privileges;
    }
    public static function prepareCase($case)
    {
        $case = preg_replace_callback('/ROLE_(A-Z_)+/', function ($match) { print_r($match);die('MATCH'); }, $case);
        return $case;
    }
    public function getAbstractRecordProperties($modelName, $modelConfig, $recordNamespace, $parent)
    {
        if (!isset($modelConfig['extends'])) {
            return array();
        }

        if (!class_exists($modelConfig['extends'])) {
            throw new \Exception("Class " . $modelConfig['extends'] . " not exist.");
        }

        if ($modelConfig['extends'] != self::ACL_RECORD_BASE_CLASS and !is_subclass_of($modelConfig['extends'], self::ACL_RECORD_BASE_CLASS)) {
            return array();
        }

        if (!isset($modelConfig['api_generation'])) {
            throw new \LogicException('Model ' . $modelName . ' must contain "api_generation" section');
        }


        $properties = array();


        //обработка привилегий
        if (!isset($modelConfig['api_generation']['privileges'])) {
            throw new \LogicException('Model ' . $modelName . ' must contain "privileges" section');
        }

        $aclPrivileges = $this->preparePrivileges($modelConfig['api_generation']['privileges']);
        $privilegesDefValue = new \Zend_CodeGenerator_Php_Property_DefaultValue();
        $privilegesDefValue->setValue($aclPrivileges);

        $privilegesProperty = new \Zend_CodeGenerator_Php_Property();
        $privilegesProperty
            ->setDefaultValue($privilegesDefValue)
            ->setName('aclPrivileges')
            ->setStatic(true)
            ->setVisibility(\Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED);
        $properties[] = $privilegesProperty;


        //видимость и редактируемость полей
        $aclActionForResource = array();
        foreach (array('view', 'add', 'edit', 'delete') as $action) {
            if (!isset($modelConfig['api_generation']['actions'][$action])) {
                throw new \LogicException('Model ' . $modelName . ' must contain "' . $action . '" section');
            }
            $aclActionForResource[$action] = $modelConfig['api_generation']['actions'][$action];

            $actionDefValue = new \Zend_CodeGenerator_Php_Property_DefaultValue();
            $actionDefValue->setValue($aclActionForResource[$action]);
            $actionProperty = new \Zend_CodeGenerator_Php_Property();
            $actionProperty
                ->setDefaultValue($actionDefValue)
                ->setName('acl' . ucfirst($action))
                ->setStatic(true)
                ->setVisibility(\Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED);
            $properties[] = $actionProperty;
        }


        //видимость и редактируемость отдельных полей

        //если не используется контроль над экшеном 'add' для полей, то он должен перенимать поведение из 'edit'
        //поэтому сначала преконфигурируем свойство add для полей
        foreach ($modelConfig['properties'] as $fieldName => $fieldConfig) {
            if (!isset($fieldConfig['api_generation']['add'])) {
                if (isset($fieldConfig['api_generation']['edit'])) {
                    $fieldConfig['api_generation']['add'] = $fieldConfig['api_generation']['edit'];
                } else {
                    $fieldConfig['api_generation']['add'] = $aclActionForResource['edit'];
                }
            }
        }

        $aclActionForFields = array();
        foreach (array('view', 'add', 'edit') as $action) {

            foreach ($modelConfig['properties'] as $fieldName => $fieldConfig) {
                if (isset($fieldConfig['api_generation'][$action])) {
                    $aclActionForFields[$action][$fieldName] = $fieldConfig['api_generation'][$action];
                } else {
                    $aclActionForFields[$action][$fieldName] = $aclActionForResource[$action];
                }
            }

            $actionFieldsDefValue = new \Zend_CodeGenerator_Php_Property_DefaultValue();
            $actionFieldsDefValue->setValue($aclActionForFields[$action]);
            $actionFieldsProperty = new \Zend_CodeGenerator_Php_Property();
            $actionFieldsProperty
                ->setDefaultValue($actionFieldsDefValue)
                ->setName('aclFields' . ucfirst($action))
                ->setStatic(true)
                ->setVisibility(\Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED);
            $properties[] = $actionFieldsProperty;
        }


        return $properties;
    }
}