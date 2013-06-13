<?php

namespace Grace\Tests\ORM\Plug;

use Grace\ORM\Service\Config\Element\MappingElement;
use Grace\ORM\Service\Config\Element\ModelElement;
use Grace\ORM\Service\Config\Element\PropertyElement;
use Grace\ORM\Service\Config\Config;

class TaxiModelsConfig extends Config
{
    public function __construct()
    {
        $this->models['TaxiPassenger'] = new ModelElement();
        $this->models['TaxiPassenger']->properties['id'] = new PropertyElement();
        $this->models['TaxiPassenger']->properties['id']->mapping = new MappingElement('int');
        $this->models['TaxiPassenger']->properties['name'] = new PropertyElement();
        $this->models['TaxiPassenger']->properties['name']->mapping = new MappingElement('string');
        $this->models['TaxiPassenger']->properties['phone'] = new PropertyElement();
        $this->models['TaxiPassenger']->properties['phone']->mapping = new MappingElement('string');
    }
}
