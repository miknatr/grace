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

class MappingElement
{
    public $relationLocalProperty;
    public $relationForeignProperty;
    public $localPropertyType;

    public function __construct($mapping)
    {
        if (preg_match('/^(\w+):(\w+)$/', $mapping, $match)) {
            $this->relationLocalProperty = $match[1];
            $this->relationForeignProperty = $match[2];
        } elseif ($mapping) {
            $this->localPropertyType = $mapping;
        } else {
            //TODO разделить поля с мэпинг фолзе и тогда тут можно срать эксепшен
        }
    }
}
