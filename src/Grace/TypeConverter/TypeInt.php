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
    public function getTypeName()
    {
        return 'int';
    }
    public function getPhpTypeName()
    {
        return 'int';
    }
    public function getDbTypeName()
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

