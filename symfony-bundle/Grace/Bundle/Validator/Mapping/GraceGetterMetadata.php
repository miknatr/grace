<?php

namespace Grace\Bundle\Validator\Mapping;

use Grace\ORM\ModelAbstract;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\GetterMetadata;

/** @property \ReflectionClass $class */
class GraceGetterMetadata extends GetterMetadata
{
    public function __construct($class, $property)
    {
        $this->class    = $class;
        $this->property = $property;
        $this->name     = $property;
    }

    public function getValue($object)
    {
        $modelClass = $this->class->getName();

        if (!($object instanceof ModelAbstract and $object instanceof $modelClass)) {
            pd($object instanceof ModelAbstract, $object instanceof $this->class, $this->class, get_class($object));
            throw new \Exception;
        }

        return $object->getProperty($this->property);
    }

    public function isPublic()
    {
        return true;
    }

    public function isProtected()
    {
        return false;
    }

    public function isPrivate()
    {
        return false;
    }

    public function getReflectionMember()
    {
        throw new \Exception;
    }

}
