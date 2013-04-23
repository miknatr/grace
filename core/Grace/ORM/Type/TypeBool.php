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

class TypeBool implements TypeInterface
{
    public function getAlias()
    {
        return 'bool';
    }
    public function getPhpType()
    {
        return 'bool';
    }
    public function getDbType()
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
        if ($value == 'f' or $value == 'off') {
            return false;
        }

        return (bool) $value;
    }
    public function convertPhpToDb($value)
    {
        return $value ? '1' : '0';
    }
}

