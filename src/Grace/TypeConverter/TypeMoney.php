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
        return 'money';
    }
    public function getPhpTypeName()
    {
        return 'string';
    }
    public function getDbTypeName()
    {
        return 'numeric(15,2)';
    }
    public function convertDbToPhp($value)
    {
        return strval($value); //it's already formatted because we save as decimal in db
    }
    public function convertOnSetter($value)
    {
        $value = floatval($value);
        $value = str_replace(',', '.', $value);
        $value = number_format($value, 2, '.', '');

        if (strlen($value) > 18) {
            throw new \OutOfRangeException('Value is out of range of money type "' . $value . '"');
        }

        return $value;
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
}

