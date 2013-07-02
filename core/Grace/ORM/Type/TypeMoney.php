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
    public function getDbToPhpConverterCode($returnIntoExpression)
    {
        return $returnIntoExpression.' (string) $value;'; //it's already formatted because we save as decimal in db
    }
    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as float');
        }

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
    public function getPhpDefaultValue()
    {
        return '0.00';
    }
}

