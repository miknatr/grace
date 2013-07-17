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

class TypeTimestamp implements TypeInterface
{
    public function getAlias()
    {
        return 'timestamp';
    }

    public function getPhpType()
    {
        return 'string';
    }

    public function getSetterPhpdocType()
    {
        return 'string';
    }

    public function getDbType()
    {
        return 'timestamp';
    }

    public function getDbToPhpConverterCode()
    {
        return '$value'; //already formatted
    }

    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as timestamp');
        }

        // on setters we just support any valid date
        try {
            $dt = new \DateTime($value);
        } catch (\Exception $e) {
            throw new ConversionImpossibleException('Invalid timestamp: ' . $value, $e->getCode(), $e);
        }

        return static::format($dt->getTimestamp());
    }

    public function convertPhpToDb($value)
    {
        return $value;
    }

    public static function format($unixtime)
    {
        return date('Y-m-d H:i:s', $unixtime);
    }

    public function getPhpDefaultValueCode()
    {
        return 'null';
    }

    public function isNullable()
    {
        return true;
    }

    public function getSqlField()
    {
        return '?f';
    }
}
