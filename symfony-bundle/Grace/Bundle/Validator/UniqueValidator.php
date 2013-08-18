<?php

namespace Grace\Bundle\Validator;

use Grace\Bundle\Validator\Constraint\Unique;
use Grace\ORM\Grace;
use Grace\ORM\ModelAbstract;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueValidator extends ConstraintValidator
{
    private $orm;

    public function __construct(Grace $orm)
    {
        $this->orm = $orm;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var $constraint Unique */

        if (!$constraint->id || !$constraint->baseClass) {
            /** @var $root Form|ModelAbstract */
            $root = $this->context->getRoot();
            /** @var $model ModelAbstract */
            $model = ($root instanceof Form) ? $root->getData() : $root;

            if (is_object($model)) {
                $constraint->id        = $model->id;
                $constraint->baseClass = $model->baseClass;
                $constraint->property  = $this->context->getCurrentProperty();
            }
        }

        $finder = $this->orm->getFinder($constraint->baseClass);
        if (!$finder) {
            throw new \LogicException("Unique validator only works with Grace models, not {$constraint->baseClass}");
        }

        if ($finder->getSelectBuilder()->eq($constraint->property, $value)->notEq('id', $constraint->id)->fetchOneOrFalse()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
