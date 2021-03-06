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

/**
 * ВНИМАНИЕ ВНИМАНИЕ ВНИМАНИЕ ЧИТАТЬ СЮДА ГЛАЗАМИ
 * в постгисе формат координат x y, то есть сначала longitude, а потом latitude
 * у них разные пределы -180:180 и -90:90
 * в связи с крайне понятной документацией постгиса, любому пытающемуся их перепутать рекоммендовано мокнуть голову в унитаз
 */
class GeoPointValue
{
    private $srid      = 0;
    private $latitude  = 0;
    private $longitude = 0;

    public function __construct($srid, $latitude, $longitude)
    {
        $this->srid      = $srid;
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    public static function createFromEWKT($ewktString) {
        if (!is_string($ewktString)) {
            throw new ConversionImpossibleException('Invalid point value type: ' . gettype($ewktString));
        }

        if (!preg_match('/^SRID=(\d+);POINT\(?(-?[\d.]+) (-?[\d.]+)\)?$/', $ewktString, $match)) {
            throw new ConversionImpossibleException('Invalid point type format: "' . $ewktString . '", should be a string like "SRID=4326;POINT(0 0)"');
        }

        $srid      = $match[1];
        $latitude  = $match[3];
        $longitude = $match[2];

        return new static($srid, $latitude, $longitude);
    }

    public static function createFromCommaSeparated($commaSeparatedCoords)
    {
        if (!is_string($commaSeparatedCoords)) {
            throw new ConversionImpossibleException('Invalid point value type: ' . gettype($commaSeparatedCoords));
        }

        if (!preg_match('/^\(?(-?[\d.]+),(-?[\d.]+)\)?$/', $commaSeparatedCoords, $match)) {
            throw new ConversionImpossibleException('Invalid point type format: "' . $commaSeparatedCoords . '", should be a string like "0,0"');
        }

        $srid      = TypeGeoPoint::SRID_WGS84;
        $latitude  = $match[1];
        $longitude = $match[2];

        return new static($srid, $latitude, $longitude);
    }

    public function __toString()
    {
        return strval($this->latitude) . ',' . strval($this->longitude);
    }

    public function toEWKT()
    {
        return "SRID=" . strval($this->srid) . ";POINT(" . strval($this->longitude) . ' ' . strval($this->latitude) . ")";
    }

    public function getSrid()
    {
        return $this->srid;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    const EARTH_RADIUS = 6371; // km
    public function getDistanceTo(GeoPointValue $point)
    {
        $dLat = deg2rad($point->getLatitude() - $this->getLatitude());
        $dLon = deg2rad($point->getLongitude() - $this->getLongitude());
        $lat1 = deg2rad($this->getLatitude());
        $lat2 = deg2rad($point->getLatitude());

        $a = sin($dLat / 2) * sin($dLat / 2) + sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $c * static::EARTH_RADIUS;
    }
}
