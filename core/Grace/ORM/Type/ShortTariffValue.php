<?php

namespace Grace\ORM\Type;

//STOPPER унести из грейса интертосные типы
class ShortTariffValue
{
    private $minPrice;
    private $unitsInMinPrice;
    private $unitPrice;
    private $unit;

    public function __construct($mixed)
    {
        if ($mixed == '') {
            return;
        }

        $parts = explode('/', $mixed);
        if (count($parts) != 4) {
            return;
        }

        $filteredUnit = $this->filterUnit($parts[3]);
        if (!$filteredUnit) {
            return;
        }

        $this->minPrice        = intval($parts[0]);
        $this->unitsInMinPrice = intval($parts[1]);
        $this->unitPrice       = intval($parts[2]);
        $this->unit            = $filteredUnit;
    }

    public function __toString()
    {
        return $this->minPrice == '' ? '' : "$this->minPrice/$this->unitsInMinPrice/$this->unitPrice/$this->unit";
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
