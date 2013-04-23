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

class TypeMoney implements TypeInterface
{
    public function getAlias()
    {
        return 'money';
    }
    public function getPhpType()
    {
        return 'string';
    }
    public function getDbType()
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

