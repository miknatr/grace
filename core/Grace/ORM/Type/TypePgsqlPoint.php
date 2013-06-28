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

class TypePgsqlPoint implements TypeInterface
{
    public function getAlias()
    {
        return 'pgsql_point';
    }

    public function getPhpType()
    {
        return '\Grace\ORM\Type\PgsqlPointValue';
    }

    public function getDbType()
    {
        return 'point';
    }

    public function convertDbToPhp($value)
    {
        if (preg_match('/^\(([\d]+),([\d]+)\)$/', $value, $match)) {
            return new PgsqlPointValue(array($match[1], $match[2]));
        }

        throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as point string');
    }

    public function convertOnSetter($value)
    {
        if ($value instanceof PgsqlPointValue) {
            return $value;
        }

        if (is_scalar($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' can not be presented as point string');
        }

        return new PgsqlPointValue($value);
    }

    public function convertPhpToDb($value)
    {
        //'PointFromWKB(POINT(?e, ?e))';//mysql
        /** @var $value PgsqlPointValue */
        return new SqlValue('POINT(?e, ?e)', array($value->getLatitude(), $value->getLongitude()));
    }
    public function getPhpDefaultValue()
    {
        return new PgsqlPointValue('0,0');
    }
}

