<?php

namespace Grace\Bundle\Validator;

use Grace\Bundle\Validator\Constraint\UniqueConstraint;
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
        /** @var $constraint UniqueConstraint */

        /** @var $root Form|ModelAbstract */
        $root = $this->context->getRoot();

        /** @var $model ModelAbstract */
        $model    = ($root instanceof Form) ? $root->getData() : $root;
        $property = $this->context->getCurrentProperty();

        $finder = $this->orm->getFinder(get_class($model));
        if (!$finder) {
            // STOPPER хня
            throw new \LogicException('файндера нет усритесь');
        }

        if ($finder->getSelectBuilder()->eq($property, $value)->notEq('id', $model->getId())->fetchOneOrFalse()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
