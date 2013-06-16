<?php

namespace Grace\Bundle\Validator\Mapping\Loader;

use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\Config\Config;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;

class GraceModelValidationLoader implements LoaderInterface
{
    private $constraintPrefixes = array();
    /** @var ClassNameProvider */
    private $classNameProvider;
    /** @var Config */
    private $config;

    public function __construct(ClassNameProvider $classNameProvider, Config $config, array $constraintPrefixes)
    {
        $this->constraintPrefixes = $constraintPrefixes;
        $this->classNameProvider = $classNameProvider;
        $this->config = $config;
    }

    public function loadClassMetadata(ClassMetadata $metadata)
    {
        /** @var \ReflectionClass $refClass */
        $refClass = $metadata->getReflectionClass();
        $baseClass = $this->classNameProvider->getBaseClass($refClass->name);

        if (!$baseClass) {
            return false;
        }

        foreach ($this->config->models[$baseClass]->properties as $propName => $property) {
            if ($property->validation) {
                if (!is_array($property->validation)) {
                    throw new \LogicException("Configuration error: $baseClass:$propName:validation must be array");
                }

                foreach ($property->validation as $constraintClass => $constraintOptions) {
                    $fullClass = $this->getFullConstraintClass($constraintClass);
                    $metadata->addGetterConstraint('get' . ucfirst($propName), new $fullClass($constraintOptions));
                }
            }
        }

        return true;
    }

    protected function getFullConstraintClass($shortConstraintClass)
    {
        foreach ($this->constraintPrefixes as $prefix) {
            if (class_exists($prefix . $shortConstraintClass)) {
                if (!is_subclass_of($prefix . $shortConstraintClass, '\Symfony\Component\Validator\Constraint')) {
                    throw new \LogicException("Configuration error: $prefix$shortConstraintClass is not constraint subclass");
                }

                return $prefix . $shortConstraintClass;
            }
        }

        throw new \LogicException("Configuration error: Full constraint class for $shortConstraintClass not found");
    }
}
