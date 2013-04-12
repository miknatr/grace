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

class TypeYear implements TypeInterface
{
    public function getTypeName()
    {
        return 'year';
    }
    public function getPhpTypeName()
    {
        return 'string';
    }
    public function getDbTypeName()
    {
        return 'numeric(4,0)';
    }
    public function convertDbToPhp($value)
    {
        return intval($value); //it's already formatted because we save as decimal in db
    }
    public function convertOnSetter($value)
    {
        $value = intval($value);

        if (strlen($value) > 9999) {
            throw new \OutOfRangeException('Value is out of range of year type "' . $value . '"');
        }

        return $value;
    }
    public function convertPhpToDb($value)
    {
        return strval($value);
    }
}

