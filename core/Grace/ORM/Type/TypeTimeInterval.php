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
        return 'string';
    }
    public function getDbType()
    {
        // 01:00:00-02:00:00
        return 'char(17)';
    }
    public function convertDbToPhp($value)
    {
        return $value;
    }
    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as time interval');
        }

        // STOPPER надо ли валидировать?
        if (!preg_match('/^\d\d:\d\d:\d\d-\d\d:\d\d:\d\d$/', $value)) {
            throw new ConversionImpossibleException('Invalid time interval "' . $value . '" (should be hh:mm:ss-hh:mm:ss)');
        }
        return $value;
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }
}

