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

class TypeBool implements TypeInterface
{
    public function getTypeName()
    {
        return 'bool';
    }
    public function getPhpTypeName()
    {
        return 'bool';
    }
    public function getDbTypeName()
    {
        return 'boolean';
    }
    public function convertDbToPhp($value)
    {
        if ($value == 'f') {
            return false;
        }

        return (bool) $value;
    }
    public function convertOnSetter($value)
    {
        return (bool) $value;
    }
    public function convertPhpToDb($value)
    {
        return $value ? '1' : '0';
    }
}

