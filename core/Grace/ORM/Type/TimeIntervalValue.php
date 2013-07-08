<?php

namespace Grace\ORM\Type;

//STOPPER унести из грейса интертосные типы
class TimeIntervalValue
{
    // inclusive, 'hh:mm:ss'
    private $timeFrom;
    private $timeTo;

    public function __construct($value)
    {
        if (!is_string($value)) {
            throw new ConversionImpossibleException('TimeInterval needs a string, ' . gettype($value) . ' given');
        }

        if (!preg_match('/^(\d\d:\d\d:\d\d)-(\d\d:\d\d:\d\d)$/', $value, $match)) {
            throw new ConversionImpossibleException('Invalid time interval "' . $value . '" (should be hh:mm:ss-hh:mm:ss)');
        }

        $this->timeFrom = $match[1];
        $this->timeTo   = $match[2];
    }

    public function __toString()
    {
        return $this->timeFrom == '' ? '' : $this->timeFrom . '-' . $this->timeTo;
    }

    /** @return string hh:mm:ss */
    public function getTimeFrom()
    {
        return $this->timeFrom;
    }

    /** @return string hh:mm:ss */
    public function getTimeTo()
    {
        return $this->timeTo;
    }
}
