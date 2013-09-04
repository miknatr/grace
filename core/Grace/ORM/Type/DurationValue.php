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

class DurationValue
{
    private $hours   = 0;
    private $minutes = 0;

    public function __construct($hours = 0, $minutes = 0)
    {
        $this->hours   = $hours;
        $this->minutes = $minutes;
    }

    public static function createFromFormattedString($formattedString) {
        if (!is_string($formattedString)) {
            throw new ConversionImpossibleException('Invalid interval value type: ' . gettype($formattedString));
        }

        if (preg_match('/^(\d{2}):(\d{2})$/', $formattedString, $match)) { // HH:ii
            return new static($match[1], $match[2]);
        } elseif (preg_match('/^(\d{2})$/', $formattedString, $match)) { // ii
            return new static(0, $match[2]);
        }

        throw new ConversionImpossibleException('Invalid interval value format: "' . $formattedString . '", should be a string like "00:05"');
    }

    public function __toString()
    {
        return sprintf('%02d:%02d', $this->hours, $this->minutes);
    }

    public function getHours()
    {
        return $this->hours;
    }
    public function getMinutes()
    {
        return $this->minutes;
    }
}
