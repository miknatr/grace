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
        // STOPPER setter can be by string too
        return '\Grace\ORM\Type\ShortTariffValue';
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

        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as tariff string');
        }

        return new ShortTariffValue($value);
    }
    public function convertPhpToDb($value)
    {
        /** @var ShortTariffValue $value */
        return (string) $value;
    }
    public function getPhpDefaultValue()
    {
        return new ShortTariffValue('');
    }
}

