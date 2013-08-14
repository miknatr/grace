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
use Grace\ORM\Type\TypeNullableBool;
use Grace\ORM\Type\TypePercent;
use Grace\ORM\Type\TypeGeoPoint;
use Grace\ORM\Type\TypeInterval;
use Grace\ORM\Type\TypeTariffOverride;
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
        //TODO это нужно в конфигурацию, некоторые чисто интертосные типы
        $this->addType(new TypeBool);
        $this->addType(new TypeNullableBool);
        $this->addType(new TypeFloat);
        $this->addType(new TypeInt);
        $this->addType(new TypeMoney);
        $this->addType(new TypePercent);
        $this->addType(new TypeGeoPoint);
        $this->addType(new TypeInterval);
        $this->addType(new TypeString);
        $this->addType(new TypeText);
        $this->addType(new TypeTimeInterval);
        $this->addType(new TypeTimestamp);
        $this->addType(new TypeYear);
        $this->addType(new TypeTariffOverride);
    }

    public function addType(TypeInterface $type)
    {
        if (isset($this->types[$type->getAlias()])) {
            throw new \LogicException('Type named "' . $type->getAlias() . '" is already added');
        }

        $this->types[$type->getAlias()] = $type;
    }

    public function hasType($alias)
    {
        return isset($this->types[$alias]);
    }

    public function getPhpType($alias)
    {
        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->getPhpType();
    }

    public function getSetterPhpdocType($alias)
    {
        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->getSetterPhpdocType();
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
        if ($value === null && !$isNullAllowed && !$this->isNullable($alias)) {
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
        // TODO переделать этот метод на генерацию кода

        //copy-paste, but we need speed
        if ($value === null && !$isNullAllowed && !$this->isNullable($alias)) {
            throw new ConversionImpossibleException('Null is not allowed');
        }

        //copy-paste, but we need speed
        if ($value === null) {
            return null;
        }

        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->convertPhpToDb($value);
    }

    public function getPhpDefaultValueCode($alias)
    {
        //If you have error, maybe type named $alias is not defined
        return $this->types[$alias]->getPhpDefaultValueCode();
    }

    public function isNullable($alias)
    {
        return $this->types[$alias]->isNullable();
    }

    public function getSqlField($alias)
    {
        return $this->types[$alias]->getSqlField();
    }
}
