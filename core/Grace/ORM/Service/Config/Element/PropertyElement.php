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

class PropertyElement
{
    /**
     * Grace hasn't got own validation service
     * So, validation from config is loaded "as is" and custom validation service has to handle it.
     * @var mixed
     */
    public $validation;

    /** @var DefaultElement */
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
    public static function create($modelName, $propertyName, $mapping, $default = null, $validation = null)
    {
        $mapping = static::parseMapping($mapping);

        if ($propertyName == 'id' && !$mapping->localPropertyType) {
            throw new \LogicException("Bad mapping: {$modelName}.{$propertyName} must have a local mapping");
        }

        $property = new PropertyElement();
        $property->rawMapping = $mapping;

        if ($validation) {
            $property->validation = $validation;
        }
        if ($default) {
            $property->default = new DefaultElement($default);
        }

        $property->isSettable = $mapping->localPropertyType || $mapping->foreignKeyTable;
        $property->isLocalInDb = $property->isSettable; // in theory isSettable can be different from isLocalInDb

        // we only allow NULL in foreign key properties and proxy-properties (which are null when the foreign key is null)
        $property->isNullable = $mapping->foreignKeyTable || $mapping->relationLocalProperty;

        $property->resolvesToModelName = $mapping->foreignKeyTable;

        return $property;
    }

    private static function parseMapping($mapping)
    {
        $me = new MappingElement();
        if (preg_match('/^(\w+):(\w+)$/', $mapping, $match)) {
            $me->relationLocalProperty = $match[1];
            $me->relationForeignProperty = $match[2];
        } elseif ($mapping[0] == '^') {
            $me->foreignKeyTable = substr($mapping, 1);
        } elseif ($mapping) {
            $me->localPropertyType = $mapping;
        } else {
            throw new \Exception('bad config: cannot parse mapping');
        }
        return $me;
    }



    //
    // RESOLVING
    //

    public static function resolveConfig(Config $config)
    {
        foreach ($config->models as $modelName => $modelConfig) {
            foreach ($modelConfig->properties as $propertyName => $propertyConfig) {
                static::resolve($config, $modelName, $propertyName, $propertyConfig); //php is love
            }
        }
    }

    private $isResolved = false;
    private static function resolve(Config $config, $modelName, $propertyName, PropertyElement $propertyConfig)
    {
        if ($propertyConfig->isResolved) {
            return;
        }

        $propertyConfig->type  = static::getPropertyType($config, $modelName, $propertyName, $propertyConfig);
        $propertyConfig->proxy = static::parseProxy($config, $modelName, $propertyName, $propertyConfig);

        if ($propertyConfig->type === null) {
            throw new \LogicException("Cannot parse type for {$modelName}.{$propertyName}");
        }

        $propertyConfig->isResolved = true;
    }

    private static function getPropertyType(Config $config, $modelName, $propName, PropertyElement $propertyConfig)
    {
        $mapping = $propertyConfig->rawMapping;

        // local field
        if ($mapping->localPropertyType) {
            return $mapping->localPropertyType;
        }

        // foreign key
        if ($mapping->foreignKeyTable) {
            // we need the type of ID of the foreign table
            $foreignProperty = $config->models[$mapping->foreignKeyTable]->properties['id'];
            static::resolve($config, $mapping->foreignKeyTable, 'id', $foreignProperty);

            return $foreignProperty->type;
        }

        // proxy field
        if ($mapping->relationLocalProperty) {
            $localProperty = $config->models[$modelName]->properties[$mapping->relationLocalProperty];
            static::resolve($config, $modelName, $mapping->relationLocalProperty, $localProperty);

            $foreignProperty = $config->models[$localProperty->resolvesToModelName]->properties[$mapping->relationForeignProperty];
            static::resolve($config, $localProperty->resolvesToModelName, $mapping->relationForeignProperty, $foreignProperty);

            return $foreignProperty->type;
        }

        throw new \Exception("bad config: HZHZHZHHZHZ $modelName, $propName");
    }

    private static function parseProxy(Config $config, $modelName, $propName, PropertyElement $propertyConfig)
    {
        $mapping = $propertyConfig->rawMapping;

        if (!$mapping->relationLocalProperty) {
            return null;
        }

        $localProperty = $config->models[$modelName]->properties[$mapping->relationLocalProperty];
        static::resolve($config, $modelName, $mapping->relationLocalProperty, $localProperty);

        $proxy = new ProxyElement();
        $proxy->localField   = $mapping->relationLocalProperty;
        $proxy->foreignTable = $localProperty->resolvesToModelName;
        $proxy->foreignField = $mapping->relationForeignProperty;

        $localProperty->dependentProxies[$propName] = $proxy;

        return $proxy;
    }
}
