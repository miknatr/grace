<?php

namespace Grace\ORM\Type;

// TODO интертосная штука, убрать из грейса
use Intertos\CoreBundle\Model\Tariff;

class TariffOverrideValue
{
    private $minPrice;
    private $unitsInMinPrice;
    private $unitPrice;
    private $unit;

    public function __construct($mixed)
    {
        if (!is_scalar($mixed)) {
            throw new ConversionImpossibleException('Value of type ' . gettype($mixed) . ' can not be presented as tariff override string');
        }

        // TODO валидация поширше

        $parts = explode('/', $mixed);

        // full override: 120/12/0/km
        if (count($parts) != 4) {
            throw new ConversionImpossibleException('Incorrect tariff override: "' . $mixed . '"');
        }

        $filteredUnit = $this->filterUnit($parts[3]);
        if (!$filteredUnit) {
            throw new ConversionImpossibleException('Incorrect unit in tariff override: "' . $mixed . '"');
        }

        $this->minPrice        = (int) $parts[0];
        $this->unitsInMinPrice = (int) $parts[1];
        $this->unitPrice       = (int) $parts[2];
        $this->unit            = $filteredUnit;
    }

    public static function createFromTariff(Tariff $tariff)
    {
        return new static($tariff->getMinPrice() . '/' . $tariff->getUnitsInMinPrice() . '/' . $tariff->getUnitPrice() . '/' . $tariff->getUnit());
    }

    public static function createFromFixedPrice($price)
    {
        return new static($price . '/0/0/km');
    }

    public function __toString()
    {
        return "{$this->minPrice}/{$this->unitsInMinPrice}/{$this->unitPrice}/{$this->unit}";
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

    public function isFixedPrice()
    {
        return empty($this->unitPrice);
    }
}
