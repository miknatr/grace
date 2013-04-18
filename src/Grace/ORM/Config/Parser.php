<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Config;

use Grace\ORM\Config\Element\ModelElement;
use Grace\ORM\Config\Element\ParentElement;
use Grace\ORM\Config\Element\PropertyElement;

class Parser
{
    protected $resources = array();
    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }
    public function getConfig()
    {
        //TODO кэширование бы
        $array = (new ConfigLoader)->load($this->resources);
        $config = new ModelsConfig;

        foreach ($array['models'] as $modelName => $modelConfig) {
            $config->models[$modelName] = new ModelElement;
            foreach ($modelConfig['properties'] as $propertyNameWithParentId => $propertyConfig) {
                $config->models[$modelName]->properties[$propertyNameWithParentId] = new PropertyElement;
                $config->models[$modelName]->properties[$propertyNameWithParentId]->mapping = $propertyConfig['mapping'];
            }
            foreach ($modelConfig['parents'] as $propertyNameWithParentId => $parentModelName) {
                $config->models[$modelName]->parents[$propertyNameWithParentId] = new ParentElement;
                $config->models[$modelName]->parents[$propertyNameWithParentId]->parentModel = $parentModelName;
            }
        }

        return $config;
    }
}
