<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\TypeConverter;

class Converter
{
    /** @var TypeInterface[] */
    protected $types = array();
    public function addType(TypeInterface $type)
    {
        if (isset($this->types[$type->getAlias()])) {
            throw new \LogicException('Type named "' . $type->getAlias() . '" is already added');
        }

        $this->types[$type->getAlias()] = $type;
    }
    public function getPhpType($alias)
    {
        $this->throwIfTypeIsNotDefined($alias);
        return $this->types[$alias]->getPhpType();
    }
    public function getDbType($alias)
    {
        $this->throwIfTypeIsNotDefined($alias);
        return $this->types[$alias]->getPhpType();
    }
    public function convertDbToPhp($alias, $value)
    {
        $this->throwIfTypeIsNotDefined($alias);
        return $this->types[$alias]->convertDbToPhp($value);
    }
    public function convertOnSetter($alias, $value)
    {
        $this->throwIfTypeIsNotDefined($alias);
        return $this->types[$alias]->convertOnSetter($value);
    }
    public function convertPhpToDb($alias, $value)
    {
        $this->throwIfTypeIsNotDefined($alias);
        return $this->types[$alias]->convertPhpToDb($value);
    }

    private function throwIfTypeIsNotDefined($alias)
    {
        if (!isset($this->types[$alias])) {
            throw new \LogicException('Type named "' . $alias . '" is not defined');
        }
    }
}

