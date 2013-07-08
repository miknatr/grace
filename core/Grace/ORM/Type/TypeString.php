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

class TypeString implements TypeInterface
{
    public function getAlias()
    {
        return 'string';
    }

    public function getPhpType()
    {
        return 'string';
    }

    public function getDbType()
    {
        return 'varchar(255)';
    }

    public function getDbToPhpConverterCode()
    {
        return '$value';
    }

    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as string');
        }

        return substr(strval($value), 0, 255);
    }

    public function convertPhpToDb($value)
    {
        return $value;
    }

    public function getPhpDefaultValueCode()
    {
        return "''";
    }

    public function isNullable()
    {
        return false;
    }
}
