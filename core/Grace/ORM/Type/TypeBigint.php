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

class TypeBigint implements TypeInterface
{
    public function getAlias()
    {
        return 'bigint';
    }

    public function getPhpType()
    {
        return 'string';
    }

    public function getSetterPhpdocType()
    {
        return 'string';
    }

    public function getDbType()
    {
        return 'bigint';
    }

    public function getDbToPhpConverterCode()
    {
        return '(string) $value';
    }

    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as integer');
        }
        return preg_replace('/[^\d-]+/', '', $value);
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
