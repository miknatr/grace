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

class TypeTimeInterval implements TypeInterface
{
    public function getAlias()
    {
        return 'time_interval';
    }

    public function getPhpType()
    {
        return '\\Grace\\ORM\\Type\\TimeIntervalValue';
    }

    public function getSetterPhpdocType()
    {
        return '\\Grace\\ORM\\Type\\TimeIntervalValue|string';
    }

    public function getDbType()
    {
        // 01:00:00-02:00:00
        return 'char(17)';
    }

    public function getDbToPhpConverterCode()
    {
        return 'new \\Grace\\ORM\\Type\\TimeIntervalValue($value)';
    }

    public function convertOnSetter($value)
    {
        if ($value instanceof TimeIntervalValue) {
            return $value;
        }

        return new TimeIntervalValue($value);
    }

    public function convertPhpToDb($value)
    {
        /** @var TimeIntervalValue $value */
        return (string) $value;
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
