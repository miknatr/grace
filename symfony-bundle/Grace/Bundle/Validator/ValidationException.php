<?php

namespace Grace\Bundle\Validator;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ValidationException extends \Exception
{
    protected $errors;

    public function __construct(ConstraintViolationList $errors, $code = 0, \Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct('Ошибка валидации' . "\n" . $this->errors->__toString(), $code, $previous);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFormattedErrors(Translator $translator)
    {
        $formattedErrors = array();
        foreach ($this->errors as $error) {
            /** @var $error ConstraintViolation */
            $formattedErrors[$error->getPropertyPath()][] = $translator->trans($error->getMessage(), array(), 'validators');
        }

        return $formattedErrors;
    }
}
