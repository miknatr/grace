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
use Grace\ORM\Type\TypeTime;
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
        $this->checkTypeAlias($alias);
        return $this->types[$alias]->getPhpType();
    }

    private function checkTypeAlias($alias)
    {
        if (!isset($this->types[$alias])) {
            throw new \LogicException('Type named "' . $alias . '" is not defined');
        }
    }

    private function checkNull($value, $isNullAllowed)
    {
        if (is_null($value) and !$isNullAllowed) {
            throw new ConversionImpossibleException('Null is not allowed');
        }
    }

    public function getDbType($alias)
    {
        $this->checkTypeAlias($alias);
        return $this->types[$alias]->getDbType();
    }

    public function convertDbToPhp($alias, $value, $isNullAllowed = false)
    {
        $this->checkNull($value, $isNullAllowed);
        $this->checkTypeAlias($alias);
        if (is_null($value)) {
            return null;
        }
        return $this->types[$alias]->convertDbToPhp($value);
    }

    public function convertOnSetter($alias, $value, $isNullAllowed = false)
    {
        $this->checkNull($value, $isNullAllowed);
        $this->checkTypeAlias($alias);
        if (is_null($value)) {
            return null;
        }
        return $this->types[$alias]->convertOnSetter($value);
    }

    public function convertPhpToDb($alias, $value, $isNullAllowed = false)
    {
        $this->checkNull($value, $isNullAllowed);
        $this->checkTypeAlias($alias);
        if (is_null($value)) {
            return null;
        }
        return $this->types[$alias]->convertPhpToDb($value);
    }

    public function getPhpDefaultValue($alias)
    {
        $this->checkTypeAlias($alias);
        return $this->types[$alias]->getPhpDefaultValue();
    }
}
