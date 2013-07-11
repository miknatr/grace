<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Service\Config\Element;

use Grace\ORM\Service\Config\Config;
use Grace\ORM\Service\Config\ConfigLoadException;
use Grace\ORM\Service\TypeConverter;

class PropertyElement
{
    /**
     * Grace hasn't got own validation service
     * So, validation from config is loaded "as is" and custom validation service has to handle it.
     * @var mixed
     */
    public $validation;

    /**
     * Definition of the default value
     *
     * Can be 'now' meaning current time when a model is created,
     * or any other string to be used in the property setter.
     *
     * @var string
     */
    public $default;

    // the property can be set (i.e. there can be a setPropName() in the model)
    public $isSettable;

    // the property is stored in the model table (there is an actual propName field in the table)
    public $isLocalInDb;

    // a type alias (see TypeConverter class)
    public $type;

    // whether the property can have a NULL value (both in DB and in the model object)
    public $isNullable;

    /**
     * Model name which current property can be resolved to, if any
     *
     * E.g. regionId can be resolved into a Region model.
     *
     * @var string
     */
    public $resolvesToModelName;

    /**
     * List of proxies that depend on current property
     *
     * If current property is a foreign key, $dependentProxies
     * will have a list of proxies that are dependent on this relation.
     *
     * Key of the array is property name that contains the proxy.
     *
     * @var ProxyElement[]
     */
    public $dependentProxies = array();

    /**
     * Parameters of mapping if the property is a proxy
     *
     * That is, the mapping is like 'regionId:name' and the property simply
     * mirrors a property of another model that is linked to current model
     * via a foreign key.
     *
     * @var ProxyElement
     */
    public $proxy;



    //
    // PARSING
    //

    /** @var MappingElement  */
    private $rawMapping;
    public static function create(TypeConverter $typeConverter, $modelName, $propertyName, $mapping, $default = null, $validation = null)
    {
        $mapping = static::parseMapping($mapping, $modelName, $propertyName, $typeConverter);

        if ($propertyName == 'id' && !$mapping->localPropertyType) {
            throw new ConfigLoadException("Property {$modelName}.{$propertyName} must have a local mapping");
        }

        $property = new PropertyElement();
        $property->rawMapping = $mapping;

        if ($validation) {
            $property->validation = $validation;
        }
        if ($default) {
            $property->default = $default;
        }

        $property->isSettable  = $mapping->localPropertyType || $mapping->foreignKeyTable;
        $property->isLocalInDb = $property->isSettable; // in theory isSettable can be different from isLocalInDb

        // we allow NULL in foreign key properties and proxy-properties (which are null when the foreign key is null)
        // also NULL is allowed when the type is nullable, which we will know after resolve()
        $property->isNullable = $mapping->foreignKeyTable || $mapping->relationLocalProperty;

        $property->resolvesToModelName = $mapping->foreignKeyTable;

        return $property;
    }

    private static function parseMapping($mapping, $modelName, $propertyName, TypeConverter $typeConverter)
    {
        $me = new MappingElement();
        if (preg_match('/^(\w+):(\w+)$/', $mapping, $match)) {
            // две строки через двоеточие — relationProp:foreignProp, прокси-поле
            $me->relationLocalProperty   = $match[1];
            $me->relationForeignProperty = $match[2];
        } elseif (strtoupper($mapping[0]) == $mapping[0]) {
            // начинается с прописной буквы — модель, т.е. внешний ключ
            $me->foreignKeyTable = $mapping;
        } elseif ($mapping) {
            // не начинается с прописной буквы — тип данных, локальное поле
            if (!$typeConverter->hasType($mapping)) {
                throw new ConfigLoadException("Incorrect type \"{$mapping}\" for {$modelName}.{$propertyName}");
            }
            $me->localPropertyType = $mapping;
        } else {
            throw new ConfigLoadException("Cannot parse mapping \"{$mapping}\" for {$modelName}.{$propertyName}");
        }
        return $me;
    }



    //
    // RESOLVING
    //

    public static function resolveConfig(Config $config, TypeConverter $typeConverter)
    {
        foreach ($config->models as $modelName => $modelConfig) {
            foreach ($modelConfig->properties as $propertyName => $propertyConfig) {
                static::resolve($config, $modelName, $propertyName, $propertyConfig, $typeConverter); //php is love
            }
        }
    }

    private $isResolved = false;
    private static function resolve(Config $config, $modelName, $propertyName, PropertyElement $propertyConfig, TypeConverter $typeConverter)
    {
        if ($propertyConfig->isResolved) {
            return;
        }

        $propertyConfig->type  = static::getPropertyType($config, $modelName, $propertyName, $propertyConfig, $typeConverter);
        $propertyConfig->proxy = static::parseProxy($config, $modelName, $propertyName, $propertyConfig, $typeConverter);

        if (!$typeConverter->hasType($propertyConfig->type)) {
            throw new ConfigLoadException("Incorrect type \"{$propertyConfig->type}\" for {$modelName}.{$propertyName}");
        }

        if ($propertyConfig->resolvesToModelName && !isset($config->models[$propertyConfig->resolvesToModelName])) {
            throw new ConfigLoadException("Incorrect model name \"{$propertyConfig->resolvesToModelName}\" for {$modelName}.{$propertyName}");
        }

        if ($typeConverter->isNullable($propertyConfig->type)) {
            $propertyConfig->isNullable = true;
        }

        $propertyConfig->isResolved = true;
    }

    private static function getPropertyType(Config $config, $modelName, $propName, PropertyElement $propertyConfig, TypeConverter $typeConverter)
    {
        $mapping = $propertyConfig->rawMapping;

        // local property
        if ($mapping->localPropertyType) {
            return $mapping->localPropertyType;
        }

        // foreign key
        if ($mapping->foreignKeyTable) {
            // we need the type of ID of the foreign table
            $foreignProperty = $config->models[$mapping->foreignKeyTable]->properties['id'];
            static::resolve($config, $mapping->foreignKeyTable, 'id', $foreignProperty, $typeConverter);

            return $foreignProperty->type;
        }

        // proxy property
        if ($mapping->relationLocalProperty) {
            $localProperty = $config->models[$modelName]->properties[$mapping->relationLocalProperty];
            static::resolve($config, $modelName, $mapping->relationLocalProperty, $localProperty, $typeConverter);

            $foreignProperty = $config->models[$localProperty->resolvesToModelName]->properties[$mapping->relationForeignProperty];
            static::resolve($config, $localProperty->resolvesToModelName, $mapping->relationForeignProperty, $foreignProperty, $typeConverter);

            return $foreignProperty->type;
        }

        throw new ConfigLoadException("Cannot parse type for {$modelName}.{$propName}");
    }

    private static function parseProxy(Config $config, $modelName, $propName, PropertyElement $propertyConfig, TypeConverter $typeConverter)
    {
        $mapping = $propertyConfig->rawMapping;

        if (!$mapping->relationLocalProperty) {
            return null;
        }

        $localProperty = $config->models[$modelName]->properties[$mapping->relationLocalProperty];
        static::resolve($config, $modelName, $mapping->relationLocalProperty, $localProperty, $typeConverter);

        $proxy = new ProxyElement();
        $proxy->localProperty   = $mapping->relationLocalProperty;
        $proxy->foreignModel    = $localProperty->resolvesToModelName;
        $proxy->foreignProperty = $mapping->relationForeignProperty;

        $localProperty->dependentProxies[$propName] = $proxy;

        return $proxy;
    }
}
