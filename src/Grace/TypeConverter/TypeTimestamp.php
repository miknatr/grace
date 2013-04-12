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

class TypeTimestamp implements TypeInterface
{
    public function getTypeName()
    {
        return 'timestamp';
    }
    public function getPhpTypeName()
    {
        return 'string';
    }
    public function getDbTypeName()
    {
        return 'timestamp';
    }
    public function convertDbToPhp($value)
    {
        return $value; //already formatted
    }
    public function convertOnSetter($value)
    {
        $dt = date_parse_from_format('Y-m-d H:i:s', $value);
        return date('Y-m-d H:i:s', mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']));
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
}

