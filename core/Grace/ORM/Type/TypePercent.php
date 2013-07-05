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

class TypePercent implements TypeInterface
{
    public function getAlias()
    {
        return 'percent';
    }
    public function getPhpType()
    {
        return 'string';
    }
    public function getDbType()
    {
        return 'numeric(3,1)';
    }
    public function getDbToPhpConverterCode()
    {
        return '(string) $value'; //it's already formatted because we save as decimal in db
    }
    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as float');
        }

        $value = floatval($value);
        $value = str_replace(',', '.', $value);
        $value = number_format($value, 1, '.', '');

        if (strlen($value) > 5) {
            throw new \OutOfRangeException('Value is out of range of percent type "' . $value . '"');
        }

        return $value;
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
    public function getPhpDefaultValueCode()
    {
        return '0.0';
    }
}

