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
        if (!is_array($value) && preg_match('/^\(([\d]+),([\d]+)\)$/', $value, $match)) {
            $value = array($value[1], $value[2]);
        }
        return new PgsqlPointValue($value);
    }
    public function convertOnSetter($value)
    {
        if ($value instanceof PgsqlPointValue) {
            return $value;
        }

        return new PgsqlPointValue($value);
    }
    public function convertPhpToDb($value)
    {
        //'PointFromWKB(POINT(?e, ?e))';//mysql
        /** @var $value PgsqlPointValue */
        return new SqlValue('(?e, ?e)', array($value->getLatitude(), $value->getLongitude()));
    }
}

