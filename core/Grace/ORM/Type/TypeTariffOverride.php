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

class TypeTariffOverride implements TypeInterface
{
    public function getAlias()
    {
        return 'tariff_override';
    }

    public function getPhpType()
    {
        return '\\Grace\\ORM\\Type\\TariffOverrideValue';
    }

    public function getSetterPhpdocType()
    {
        return '\\Grace\\ORM\\Type\\TariffOverrideValue|string';
    }

    public function getDbType()
    {
        return 'varchar(255)';
    }

    public function getDbToPhpConverterCode()
    {
        return 'new \\Grace\\ORM\\Type\\TariffOverrideValue($value)';
    }

    public function convertOnSetter($value)
    {
        if ($value instanceof TariffOverrideValue) {
            return $value;
        }

        return new TariffOverrideValue($value);
    }

    public function convertPhpToDb($value)
    {
        /** @var TariffOverrideValue $value */
        return (string) $value;
    }

    public function getPhpDefaultValueCode()
    {
        return "new \\Grace\\ORM\\Type\\TariffOverrideValue('')";
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
