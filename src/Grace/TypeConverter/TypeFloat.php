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

class TypeFloat implements TypeInterface
{
    public function getTypeName()
    {
        return 'float';
    }
    public function getPhpTypeName()
    {
        return 'float';
    }
    public function getDbTypeName()
    {
        return 'double';
    }
    public function convertDbToPhp($value)
    {
        return floatval($value);
    }
    public function convertOnSetter($value)
    {
        return floatval($value);
    }
    public function convertPhpToDb($value)
    {
        return strval($value);
    }
}

