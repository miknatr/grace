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
        if (!($constraint instanceof Unique)) {
            throw new \LogicException("Unique validator only works with Unique constraint");
        }

        /** @var $root Form|ModelAbstract */
        $root = $this->context->getRoot();
        /** @var $model ModelAbstract */
        $model = ($root instanceof Form) ? $root->getData() : $root;

        if (!($model instanceof ModelAbstract)) {
            $class = get_class($model);
            throw new \LogicException("Unique validator only works with Grace models, not $class");
        }

        $finder = $this->orm->getFinder($model->baseClass);
        if (!$finder) {
            throw new \LogicException("Unique validator only works with Grace models, not {$model->baseClass}");
        }

        $secondModel = $finder->getSelectBuilder()
            ->eq($this->context->getCurrentProperty(), $value)
            ->notEq('id', $model->id)
            ->fetchOneOrFalse()
        ;

        if ($secondModel) {
            $this->context->addViolation($constraint->message);
        }
    }
}
