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

class TypeShortTariff implements TypeInterface
{
    public function getAlias()
    {
        return 'short_tariff';
    }

    public function getPhpType()
    {
        return '\\Grace\\ORM\\Type\\ShortTariffValue';
    }

    public function getSetterPhpdocType()
    {
        return '\\Grace\\ORM\\Type\\ShortTariffValue|string';
    }

    public function getDbType()
    {
        return 'varchar(255)';
    }

    public function getDbToPhpConverterCode()
    {
        return 'new \\Grace\\ORM\\Type\\ShortTariffValue($value)';
    }

    public function convertOnSetter($value)
    {
        if ($value instanceof ShortTariffValue) {
            return $value;
        }

        return new ShortTariffValue($value);
    }

    public function convertPhpToDb($value)
    {
        /** @var ShortTariffValue $value */
        return (string) $value;
    }

    public function getPhpDefaultValueCode()
    {
        return "new \\Grace\\ORM\\Type\\ShortTariffValue('')";
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
