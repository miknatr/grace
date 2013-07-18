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

class PgsqlGeographyPointValue
{
    private $latitude  = 0;
    private $longitude = 0;

    public function __construct($coords)
    {
        if (!is_string($coords)) {
            throw new ConversionImpossibleException('Invalid point value type: ' . gettype($coords));
        }

        if (!preg_match('/^SRID=\d+;POINT\(?(\d+) (\d+)\)?$/', $coords, $match)) {
            throw new ConversionImpossibleException('Invalid point type format: "' . $coords . '", should be a string like "SRID=4326;POINT(0 0)"');
        }

        $this->latitude  = $match[1];
        $this->longitude = $match[2];
    }

    public function __toString()
    {
        return "SRID=" . TypePgsqlGeographyPoint::SRID_WGS84 . ";POINT(" . strval($this->latitude) . ' ' . strval($this->longitude) . ")";
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }
}
