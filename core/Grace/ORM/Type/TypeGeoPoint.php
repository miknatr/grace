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

class TypeGeoPoint implements TypeInterface
{
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
        return 'point';
    }

    public function getDbToPhpConverterCode()
    {
        return 'new \\Grace\\ORM\\Type\\GeoPointValue($value)';
    }

    public function convertOnSetter($value)
    {
        if ($value instanceof GeoPointValue) {
            return $value;
        }

        if (!is_string($value)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($value) . ' should be presented as a point string like "0,0"');
        }

        return new GeoPointValue($value);
    }

    public function convertPhpToDb($value)
    {
        //'PointFromWKB(POINT(?e, ?e))';//mysql
        /** @var $value GeoPointValue */
        return new SqlValue('POINT(?e, ?e)', array($value->getLatitude(), $value->getLongitude()));
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
        return '?f';
    }
}
