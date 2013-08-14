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

class IntervalValue
{
    private $hours   = 0;
    private $minutes = 0;
    private $seconds = 0;

    public function __construct($seconds = 0, $minutes = 0, $hours = 0)
    {
        $this->hours   = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
    }

    public static function createFromFormattedString($formattedString) {
        if (!is_string($formattedString)) {
            throw new ConversionImpossibleException('Invalid interval value type: ' . gettype($formattedString));
        }

        if (!preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $formattedString, $match)) {
            throw new ConversionImpossibleException('Invalid interval value format: "' . $formattedString . '", should be a string like "00:05:10"');
        }

        $hours   = $match[1];
        $minutes = $match[2];
        $seconds = $match[3];

        return new self($seconds, $minutes, $hours);
    }

    public function __toString()
    {
        return sprintf('%02d:%02d:%02d', $this->hours, $this->minutes, $this->seconds);
    }

    public function getHours()
    {
        return $this->hours;
    }
    public function getMinutes()
    {
        return $this->minutes;
    }
    public function getSeconds()
    {
        return $this->seconds;
    }
}
