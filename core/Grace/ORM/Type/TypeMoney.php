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

    public function getSetterPhpdocType()
    {
        return 'number|string';
    }

    public function getDbType()
    {
        return 'numeric(15,2)';
    }

    public function getDbToPhpConverterCode()
    {
        return '(string) $value'; //it's already formatted because we save as decimal in db
    }

    public function convertOnSetter($value)
    {
        return static::format($value);
    }

    public function convertPhpToDb($value)
    {
        return $value;
    }

    public function getPhpDefaultValueCode()
    {
        return '0.00';
    }

    public static function format($amount)
    {
        if (!is_scalar($amount)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($amount) . ' can not be presented as float');
        }

        $amount = (float) str_replace(',', '.', $amount);
        $amount = number_format($amount, 2, '.', '');

        if (strlen($amount) > 18) {
            throw new \OutOfRangeException('Value is out of range of money type "' . $amount . '"');
        }

        return $amount;
    }

    public function isNullable()
    {
        return false;
    }

    public function getSqlField()
    {
        return '?f';
    }
}
