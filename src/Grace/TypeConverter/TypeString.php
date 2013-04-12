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

class TypeString implements TypeInterface
{
    public function getTypeName()
    {
        return 'string';
    }
    public function getPhpTypeName()
    {
        return 'string';
    }
    public function getDbTypeName()
    {
        return 'varchar(255)';
    }
    public function convertDbToPhp($value)
    {
        return $value;
    }
    public function convertOnSetter($value)
    {
        return substr(strval($value), 255);
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
}
