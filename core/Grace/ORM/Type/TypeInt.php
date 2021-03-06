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

class TypeInt implements TypeInterface
{
    public function getAlias()
    {
        return 'int';
    }

    public function getPhpType()
    {
        return 'int';
    }

    public function getSetterPhpdocType()
    {
        return 'int';
    }

    public function getDbType()
    {
        return 'integer';
    }

    public function getDbToPhpConverterCode()
    {
        return '(int) $value';
    }

    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as integer');
        }
        return (int) $value;
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

    public function getSqlField()
    {
        return '?f';
    }
}
