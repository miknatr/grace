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
