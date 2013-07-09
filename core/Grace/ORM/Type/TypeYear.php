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

class TypeYear implements TypeInterface
{
    public function getAlias()
    {
        return 'year';
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
        return 'numeric(4,0)';
    }

    public function getDbToPhpConverterCode()
    {
        return '(int) $value'; //it's already formatted because we save as decimal in db
    }

    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as year');
        }

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

    public function getPhpDefaultValueCode()
    {
        return 'null';
    }

    public function isNullable()
    {
        return true;
    }
}
