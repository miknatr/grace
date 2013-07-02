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
    public function getDbType()
    {
        return 'timestamp';
    }
    public function getDbToPhpConverterCode($returnIntoExpression)
    {
        return $returnIntoExpression.' $value;'; //already formatted
    }
    public function convertOnSetter($value)
    {
        if (!is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as timestamp');
        }

        // STOPPER нормальная валидация времени
        $dt = date_parse_from_format('Y-m-d H:i:s', $value);
        return static::format(mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']));
    }
    public function convertPhpToDb($value)
    {
        return $value;
    }

    public static function format($unixtime)
    {
        return date('Y-m-d H:i:s', $unixtime);
    }
    public function getPhpDefaultValue()
    {
        //STOPPER ???
        return '1970-01-01 00:00:00';
    }
}
