<?php

namespace Grace\Bundle\ApiBundle\Generator\GeneratorPlugin;

use Grace\Generator\GeneratorPlugin\PluginAbstract;

class AclGraceCommandPlugin extends PluginAbstract
{
    const ACL_RECORD_BASE_CLASS = '\\Grace\\Bundle\\ApiBundle\\Model\\ResourceAbstract';

    public function prepareModelConfig($modelName, $modelConfig)
    {
        if (isset($modelConfig['extends']) and $modelConfig['extends'] == 'api_generation') {
            $modelConfig['extends'] = self::ACL_RECORD_BASE_CLASS;
        }

        return $modelConfig;
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

        if (!isset($modelConfig['api_privileges']) or !isset($modelConfig['api_actions'])) {
            throw new \LogicException('Model ' . $modelName . ' must contain "api_generation" and "api_actions" sections');
        }


        $properties = array();


        //обработка привилегий

        $aclPrivileges = $modelConfig['api_privileges'];
        $privilegesDefValue = new \Zend_CodeGenerator_Php_Property_DefaultValue();
        $privilegesDefValue->setValue($aclPrivileges);

        $privilegesProperty = new \Zend_CodeGenerator_Php_Property();
        $privilegesProperty
            ->setDefaultValue($privilegesDefValue)
            ->setName('aclPrivileges')
            ->setStatic(true)
            ->setVisibility(\Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED);
        $properties[] = $privilegesProperty;


        //api_broadcast_changes (aka nodejs-able)

        $broadcastChangesProperty = new \Zend_CodeGenerator_Php_Property();
        $broadcastChangesProperty
            ->setDefaultValue((new \Zend_CodeGenerator_Php_Property_DefaultValue())->setValue(!empty($modelConfig['api_broadcast_changes'])))
            ->setName('apiBroadcastChanges')
            ->setStatic(true)
            ->setVisibility(\Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED);
        $properties[] = $broadcastChangesProperty;


        //видимость и редактируемость полей
        $aclActionForResource = array();
        $modelConfig['api_actions']['view'] = array_keys($modelConfig['api_privileges']);

        foreach (array('view', 'add', 'edit', 'delete') as $action) {
            if (!isset($modelConfig['api_actions'][$action])) {
                throw new \LogicException('Model ' . $modelName . ' must contain "' . $action . '" section');
            }
            $aclActionForResource[$action] = $modelConfig['api_actions'][$action];

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
