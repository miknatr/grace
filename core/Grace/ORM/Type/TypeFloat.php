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

class TypeFloat implements TypeInterface
{
    public function getAlias()
    {
        return 'float';
    }
    public function getPhpType()
    {
        return 'float';
    }
    public function getDbType()
    {
        return 'float';
    }
    public function getDbToPhpConverterCode($returnIntoExpression)
    {
        return $returnIntoExpression.' (float) $value;';
    }
    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as float');
        }
        return floatval($value);
    }
    public function convertPhpToDb($value)
    {
        return strval($value);
    }
    public function getPhpDefaultValue()
    {
        return 0;
    }
}

