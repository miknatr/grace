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

class TypeTimeMin implements TypeInterface
{
    public function getAlias()
    {
        return 'time_min';
    }
    public function getPhpType()
    {
        return 'string';
    }
    public function getDbType()
    {
        return 'char(5)';
    }
    public function convertDbToPhp($value)
    {
        return $value; //it's already formatted because we save as decimal in db
    }
    public function convertOnSetter($value)
    {
        $dt = date_parse_from_format('H:i', $value);
        return date('H:i', mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']));
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
}

