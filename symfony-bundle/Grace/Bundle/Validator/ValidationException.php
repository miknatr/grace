<?php

namespace Grace\Bundle\Validator;

use Symfony\Component\Validator\ConstraintViolationList;

class ValidationException extends \Exception
{
    protected $errors;

    public function __construct(ConstraintViolationList $errors, $code = 0, \Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct('Ошибка валидации', $code, $previous);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
