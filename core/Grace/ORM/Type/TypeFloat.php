<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Type;

class TypeFloat implements TypeInterface
{
    public function getAlias()
    {
        return 'float';
    }

    public function getPhpType()
    {
        return 'float';
    }

    public function getSetterPhpdocType()
    {
        return 'number|string';
    }

    public function getDbType()
    {
        return 'float';
    }

    public function getDbToPhpConverterCode()
    {
        return '(float) $value';
    }

    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as float');
        }
        return (float) $value;
    }

    public function convertPhpToDb($value)
    {
        return strval($value);
    }

    public function getPhpDefaultValueCode()
    {
        return '0';
    }

    public function isNullable()
    {
        return false;
    }
}
