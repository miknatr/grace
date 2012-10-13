<?php

namespace Grace\Bundle\CommonBundle\Validator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Grace\ORM\ManagerAbstract;
use Grace\ORM\ExceptionNoResult;

class UniqueValidator extends ConstraintValidator
{
    private $orm;

    public function __construct(ManagerAbstract $orm)
    {
        $this->orm = $orm;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var $root \Symfony\Component\Form\Form|\Grace\ORM\Record */
        $root = $this->context->getRoot();

        /** @var $origObject \Grace\ORM\Record */
        if($root instanceof \Symfony\Component\Form\Form) {
            $origObject = $root->getData();
        } else {
            $origObject = $root;
        }

        $entity = explode('\\', get_class($origObject));
        $entity = $entity[count($entity) - 1];

        $property = $this->context->getCurrentProperty();

        $finderGetter = 'get' . $entity . 'Finder';

        if (method_exists($this->orm, $finderGetter)) {
            try {
                $id = $origObject->getId();

                $object = $this->orm
                    ->$finderGetter()
                    ->getSelectBuilder()
                    ->eq($property, $value)
                    ->notEq('id', $id)
                    ->fetchOne();
            } catch (ExceptionNoResult $e) {
                return;
            }
        }
        $this->context->addViolation($constraint->message);
    }
}