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

class PgsqlPointValue
{
    private $latitude = 0;
    private $longitude = 0;

    public function __construct($coords)
    {
        //STOPPER не массив, а строка, нужно наделать парсинг
        if (count($coords) != 2 or !isset($coords[0]) or !isset($coords[1])) {
            throw new \BadMethodCallException('Invalid point type format "' . print_r($coords, true) . '"');
        }
        $this->latitude  = $coords[0];
        $this->longitude = $coords[1];
    }

    public function __toString()
    {
        return strval($this->latitude) . ',' . strval($this->longitude);
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
