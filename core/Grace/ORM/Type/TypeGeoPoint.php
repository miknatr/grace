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

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\DBAL\Exception\QueryException;
use Grace\SQLBuilder\SqlValue\SqlValue;

class TypeGeoPoint implements TypeInterface
{
    const SRID_WGS84 = 4326; // Default SRID. Others are not yet supported by PostGIS in certain operations.

    public function getAlias()
    {
        return 'geo_point';
    }

    public function getPhpType()
    {
        return '\\Grace\\ORM\\Type\\GeoPointValue';
    }

    public function getSetterPhpdocType()
    {
        return '\\Grace\\ORM\\Type\\GeoPointValue|string';
    }

    public function getDbType()
    {
        return 'geography(point,'.static::SRID_WGS84.')';
    }

    public function getDbToPhpConverterCode()
    {
        return '\\Grace\\ORM\\Type\\GeoPointValue::createFromEWKT($value)';
    }

    public function convertOnSetter($value)
    {
        if ($value instanceof GeoPointValue) {
            return $value;
        }

        if (!is_string($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' should be presented as a EWKT string like "SRID=4326;POINT(0 0)" or a comma-separated string like "0,0"');
        }

        try {
            return GeoPointValue::createFromEWKT($value);
        } catch (ConversionImpossibleException $e) {
            return GeoPointValue::createFromCommaSeparated($value);
        }
    }

    public function convertPhpToDb($value)
    {
        //'PointFromWKB(POINT(?e, ?e))';//mysql
        /** @var $value GeoPointValue */
        return new SqlValue("ST_GeographyFromText(?q)", array($value->toEWKT()));
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

    public static function initPostgis(ConnectionInterface $db)
    {
        // initialize PostGIS in the DB if we can
        // TODO find a way to check if postgis is installed correctly
        try {
            $db->execute('CREATE EXTENSION IF NOT EXISTS postgis');
        } catch (QueryException $ignored) {
        }
    }
}
