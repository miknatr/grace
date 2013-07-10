<?php

namespace Grace\ORM\Type;

class ShortTariffValue
{
    private $minPrice;
    private $unitsInMinPrice;
    private $unitPrice;
    private $unit;

    public function __construct($mixed)
    {
        if (!is_scalar($mixed)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($mixed) . ' can not be presented as tariff string');
        }

        // TODO валидация поширше

        $parts = explode('/', $mixed);
        if (count($parts) != 4) {
            throw new ConversionImpossibleException('Incorrect short tariff: "'.$mixed.'"');
        }

        $filteredUnit = $this->filterUnit($parts[3]);
        if (!$filteredUnit) {
            throw new ConversionImpossibleException('Incorrect unit in short tariff: "'.$mixed.'"');
        }

        $this->minPrice        = intval($parts[0]);
        $this->unitsInMinPrice = intval($parts[1]);
        $this->unitPrice       = intval($parts[2]);
        $this->unit            = $filteredUnit;
    }

    public function __toString()
    {
        return "$this->minPrice/$this->unitsInMinPrice/$this->unitPrice/$this->unit";
    }

    private function filterUnit($unit)
    {
        if (!in_array($unit, array('km', 'min'))) {
            return null;
        }

        return $unit;
    }

    public function getMinPrice()
    {
        return $this->minPrice;
    }
    public function getUnitsInMinPrice()
    {
        return $this->unitsInMinPrice;
    }
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }
    public function getUnit()
    {
        return $this->unit;
    }
}
