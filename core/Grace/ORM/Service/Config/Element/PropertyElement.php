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

class PropertyElement
{
    /**
     * Grace haven't got own validation service
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
}
