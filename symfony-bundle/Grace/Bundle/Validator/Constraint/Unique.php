<?php

namespace Grace\Bundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class Unique extends Constraint
{
    public $message = 'This value is already used';
    // TODO IS-644 хак, пока не можем валидровать целиком, хочется передавать контекст
    public $id;
    public $baseClass;
    public $property;

    public function validatedBy()
    {
        return 'validator.grace_validator_unique';
    }

    public function requiredOptions()
    {
        return array();
    }

    public function targets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
