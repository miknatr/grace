<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Service;

use Grace\ORM\Type\ConversionImpossibleException;
use Grace\ORM\Type\TypeBool;
use Grace\ORM\Type\TypeFloat;
use Grace\ORM\Type\TypeInt;
use Grace\ORM\Type\TypeInterface;
use Grace\ORM\Type\TypeMoney;
use Grace\ORM\Type\TypePercent;
use Grace\ORM\Type\TypePgsqlPoint;
use Grace\ORM\Type\TypeShortTariff;
use Grace\ORM\Type\TypeString;
use Grace\ORM\Type\TypeText;
use Grace\ORM\Type\TypeTimeInterval;
use Grace\ORM\Type\TypeTimestamp;
use Grace\ORM\Type\TypeYear;

class TypeConverter
{
    /** @var TypeInterface[] */
    protected $types = array();

    public function __construct()
    {
        //STOPPER это нужно в конфигурацию
        $this->addType(new TypeBool);
        $this->addType(new TypeFloat);
        $this->addType(new TypeInt);
        $this->addType(new TypeMoney);
        $this->addType(new TypePercent);
        $this->addType(new TypePgsqlPoint);
        $this->addType(new TypeString);
        $this->addType(new TypeText);
        $this->addType(new TypeTimeInterval);
        $this->addType(new TypeTimestamp);
        $this->addType(new TypeYear);
        $this->addType(new TypeShortTariff);
    }

    public function addType(TypeInterface $type)
    {
        if (isset($this->types[$type->getAlias()])) {
            throw new \LogicException('Type named "' . $type->getAlias() . '" is already added');
        }

        $this->types[$type->getAlias()] = $type;
    }

    public function getPhpType($alias)
    {
        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->getPhpType();
    }

    public function getDbType($alias)
    {
        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->getDbType();
    }

    public function getDbToPhpConverterCode($alias)
    {
        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->getDbToPhpConverterCode();
    }

    public function convertOnSetter($alias, $value, $isNullAllowed = false)
    {
        //copy-paste, but we need speed
        if (!$isNullAllowed and $value === null) {
            throw new ConversionImpossibleException('Null is not allowed');
        }

        //copy-paste, but we need speed
        if ($value === null) {
            return null;
        }

        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->convertOnSetter($value);
    }

    public function convertPhpToDb($alias, $value, $isNullAllowed = false)
    {
        //copy-paste, but we need speed
        if (!$isNullAllowed and $value === null) {
            throw new ConversionImpossibleException('Null is not allowed');
        }

        //copy-paste, but we need speed
        if ($value === null) {
            return null;
        }

        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->convertPhpToDb($value);
    }

    public function getPhpDefaultValue($alias)
    {
        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->getPhpDefaultValue();
    }
}
