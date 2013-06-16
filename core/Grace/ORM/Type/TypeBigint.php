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
    public function getDbType()
    {
        return 'bigint';
    }
    public function convertDbToPhp($value)
    {
        return strval($value);
    }
    public function convertOnSetter($value)
    {
        return strval(preg_replace('/[^\d-]+/', '', $value));
    }
    public function convertPhpToDb($value)
    {
        return strval($value);
    }
}
