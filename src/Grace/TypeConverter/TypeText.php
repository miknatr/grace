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
    public function getAlias()
    {
        return 'text';
    }
    public function getPhpType()
    {
        return 'string';
    }
    public function getDbType()
    {
        return 'text';
    }
    public function convertDbToPhp($value)
    {
        return $value;
    }
    public function convertOnSetter($value)
    {
        return strval($value);
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
}
