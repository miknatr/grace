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

class TypeTime implements TypeInterface
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
        return 'char(8)';
    }
    public function convertDbToPhp($value)
    {
        return $value; //it's already formatted because we save as decimal in db
    }
    public function convertOnSetter($value)
    {
        if (is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as time');
        }

        $dt = date_parse_from_format('H:i:s', $value);
        return date('H:i:s', mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']));
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
}

