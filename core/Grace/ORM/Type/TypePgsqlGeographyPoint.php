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

class TypePgsqlGeographyPoint implements TypeInterface
{
    const SRID_WGS84 = 4326;

    public function getAlias()
    {
        return 'pgsql_geography_point';
    }

    public function getPhpType()
    {
        return '\\Grace\\ORM\\Type\\PgsqlGeographyPointValue';
    }

    public function getSetterPhpdocType()
    {
        return '\\Grace\\ORM\\Type\\PgsqlGeographyPointValue|string';
    }

    public function getDbType()
    {
        return 'geography(point,'.static::SRID_WGS84.')';
    }

    public function getDbToPhpConverterCode()
    {
        return 'new \\Grace\\ORM\\Type\\PgsqlGeographyPointValue($value)';
    }

    public function convertOnSetter($value)
    {
        if ($value instanceof PgsqlGeographyPointValue) {
            return $value;
        }

        if (!is_string($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' should be presented as a point string like "SRID=4326;POINT(0 0)"');
        }

        return new PgsqlGeographyPointValue($value);
    }

    public function convertPhpToDb($value)
    {
        //'PointFromWKB(POINT(?e, ?e))';//mysql
        /** @var $value PgsqlGeographyPointValue */
        return new SqlValue("ST_GeographyFromText('SRID=?e;POINT(?e ?e)')", array(static::SRID_WGS84, $value->getLatitude(), $value->getLongitude()));
    }

    public function getPhpDefaultValueCode()
    {
        return 'null';
    }

    public function isNullable()
    {
        return true;
    }

    public function getSqlField()
    {
        return 'ST_AsEWKT(?f)';
    }
}
