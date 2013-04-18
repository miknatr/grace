<?php

namespace Grace\Bundle\CommonBundle\Validator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Grace\ORM\ORMManagerAbstract;
use Grace\ORM\ExceptionNoResult;

class UniqueValidator extends ConstraintValidator
{
    private $orm;

    public function __construct(ORMManagerAbstract $orm)
    {
        $this->orm = $orm;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var $root \Symfony\Component\Form\Form|\Grace\ORM\RecordAbstract */
        $root = $this->context->getRoot();

        /** @var $origObject \Grace\ORM\RecordAbstract */
        if($root instanceof \Symfony\Component\Form\Form) {
            $origObject = $root->getData();
        } else {
            $origObject = $root;
        }

        $entity = explode('\\', get_class($origObject));
        $entity = $entity[count($entity) - 1];

        $property = $this->context->getCurrentProperty();

        //STOPPER выпилить такие места
        $finderGetter = 'get' . $entity . 'Finder';

        if (method_exists($this->orm, $finderGetter)) {
            $id = $origObject->getId();

            return (bool) $this->orm
                ->$finderGetter()
                ->getSelectBuilder()
                ->eq($property, $value)
                ->notEq('id', $id)
                ->fetchOneOrFalse();
        }
        $this->context->addViolation($constraint->message);
    }
}
