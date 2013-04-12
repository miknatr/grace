<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\TypeConverter;

use Grace\SQLBuilder\SqlValue;

class TypePgsqlPoint implements TypeInterface
{
    public function getTypeName()
    {
        return 'pgsql_point';
    }
    public function getPhpTypeName()
    {
        return '\Grace\TypeConverter\PgsqlPointValue';
    }
    public function getDbTypeName()
    {
        return 'point';
    }
    public function convertDbToPhp($value)
    {
        return new PgsqlPointValue($value);
    }
    public function convertOnSetter($value)
    {
        return new PgsqlPointValue($value);
    }
    public function convertPhpToDb($value)
    {
        //'PointFromWKB(POINT(?e, ?e))';//mysql
        /** @var $value PgsqlPointValue */
        return new SqlValue('(?e, ?e)', array($value->getLatitude(), $value->getLongitude()));
    }
}

