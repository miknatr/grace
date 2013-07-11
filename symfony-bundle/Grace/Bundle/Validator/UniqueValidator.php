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

        /** @var $root Form|ModelAbstract */
        $root = $this->context->getRoot();

        /** @var $model ModelAbstract */
        $model    = ($root instanceof Form) ? $root->getData() : $root;
        $property = $this->context->getCurrentProperty();

        $class  = get_class($model);
        $finder = $this->orm->getFinder($class);
        if (!$finder) {
            if ($model instanceof ModelAbstract) {
                throw new \LogicException("Grace finder is not found for $class");
            }

            throw new \LogicException("Unique validator only works with Grace models, not $class");
        }

        if ($finder->getSelectBuilder()->eq($property, $value)->notEq('id', $model->id)->fetchOneOrFalse()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
