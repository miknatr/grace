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
        // STOPPER setter can be by string too
        return '\\Grace\\ORM\\Type\\TimeIntervalValue';
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
        return "new \\Grace\\ORM\\Type\\TimeIntervalValue('')";
    }
}

