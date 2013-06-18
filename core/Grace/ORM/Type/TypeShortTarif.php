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

class TypeShortTarif implements TypeInterface
{
    public function getAlias()
    {
        return 'short_tarif';
    }
    public function getPhpType()
    {
        return '\Grace\ORM\Type\ShortTarifValue';
    }
    public function getDbType()
    {
        return 'varchar(255)';
    }
    public function convertDbToPhp($value)
    {
        return new ShortTarifValue($value);
    }
    public function convertOnSetter($value)
    {
        if ($value instanceof ShortTarifValue) {
            return $value;
        }

        return new ShortTarifValue($value);
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
}

