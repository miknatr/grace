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
    public function getDbType()
    {
        return 'integer';
    }
    public function convertDbToPhp($value)
    {
        return intval($value);
    }
    public function convertOnSetter($value)
    {
        return intval($value);
    }
    public function convertPhpToDb($value)
    {
        return strval($value);
    }
}

