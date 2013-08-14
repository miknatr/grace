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

use Grace\SQLBuilder\SqlValue\SqlValue;

class TypeDuration implements TypeInterface
{
    public function getAlias()
    {
        return 'duration';
    }

    public function getPhpType()
    {
        return '\\Grace\\ORM\\Type\\DurationValue';
    }

    public function getSetterPhpdocType()
    {
        return '\\Grace\\ORM\\Type\\DurationValue|string';
    }

    public function getDbType()
    {
        return 'interval';
    }

    public function getDbToPhpConverterCode()
    {
        return '\\Grace\\ORM\\Type\\DurationValue::createFromFormattedString($value)';
    }

    public function convertOnSetter($value)
    {
        if ($value instanceof DurationValue) {
            return $value;
        }

        if (!is_string($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' should be presented as a Duration string like "00:05:10"');
        }

        return DurationValue::createFromFormattedString($value);
    }

    /**
     * @param DurationValue $value
     * @return SqlValue
     */
    public function convertPhpToDb($value)
    {
        return new SqlValue("'?e hours ?e minutes ?e seconds'", array($value->getHours(), $value->getMinutes(), $value->getSeconds()));
    }

    public function getPhpDefaultValueCode()
    {
        return 'null';
    }

    public function isNullable()
    {
        return true;
    }

    const OUTPUT_FORMAT = 'HH24:MI:SS'; // 00:05:10
    public function getSqlField()
    {
        return "to_char(?f, '".static::OUTPUT_FORMAT."')";
    }
}
