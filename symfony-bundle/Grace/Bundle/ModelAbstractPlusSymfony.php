<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\Bundle;

use Grace\Bundle\GracePlusSymfony;
use Grace\Bundle\Validator\Constraint\Unique;
use Grace\Bundle\Validator\ValidationException;
use Grace\ORM\Grace;
use Grace\ORM\ModelAbstract;
use Intertos\CoreBundle\Security\Core\User\UserAbstract;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\GetterMetadata;

/** @property GracePlusSymfony $orm */
abstract class ModelAbstractPlusSymfony extends ModelAbstract
{
    private $needsInitCreatedModel = false;

    public function __construct($id = null, array $dbArray = null, $baseClass, Grace $orm)
    {
        parent::__construct($id, $dbArray, $baseClass, $orm);
        if ($dbArray === null) {
            $this->needsInitCreatedModel = true;
        }
    }

    protected function initCreatedModel()
    {
    }

    /**
     * @throws Validator\ValidationException
     * @return $this
     */
    public function ensureValid()
    {
        if ($this->needsInitCreatedModel) {
            $constraintViolationList = $this->validateProperties($this->properties);
        } else {
            $constraintViolationList = $this->validateProperties(array_diff($this->properties, $this->originalProperties));
        }

        if ($constraintViolationList->count() != 0) {
            $this->revert();
            throw new ValidationException($constraintViolationList);
        }

        if ($this->needsInitCreatedModel) {
            $this->initCreatedModel();
            $this->needsInitCreatedModel = false;
        }

        return $this;
    }

    /**
     * @param array $properties
     * @return ConstraintViolationList
     */
    public function validateProperties(array $properties)
    {
        $validator = $this->orm->validator;

        /** @var ClassMetadata $classMetadata */
        $classMetadata = $validator->getMetadataFactory()->getClassMetadata(get_class($this));

        $fieldConstraints = array();

        foreach (array_keys($properties) as $fieldName) {
            if (!isset($classMetadata->members[$fieldName])) {
                // no constraints for this property
                $fieldConstraints[$fieldName] = array();
                continue;
            }
            $fieldConstraints[$fieldName] = array();
            foreach ($classMetadata->members[$fieldName] as $fieldMetadata) {
                /** @var GetterMetadata $fieldMetadata */
                $fieldConstraints[$fieldName] = array_values($fieldMetadata->constraints);
                // TODO IS-644 хак для уникального валидатора, надо завязывать с этой валидацией отдельных пропертей и валидировать весь объект с контекстом
                foreach ($fieldConstraints[$fieldName] as $constraint) {
                    if ($constraint instanceof Unique) {
                        $constraint->id        = $this->id;
                        $constraint->baseClass = $this->baseClass;
                        $constraint->property  = $fieldName;
                    }
                }
            }
        }

        $constraint = new Collection(array('fields' => $fieldConstraints));

        $listWithWrongNames = $validator->validateValue($properties, $constraint);

        $properList = new ConstraintViolationList();

        foreach ($listWithWrongNames as $k => $v) {
            /** @var ConstraintViolation $v */
            $properList[$k] = new ConstraintViolation(
                $v->getMessageTemplate(),
                $v->getMessageParameters(),
                $v->getRoot(),
                substr($v->getPropertyPath(), 1, -1), // [fieldName] => fieldName
                $v->getInvalidValue(),
                $v->getMessagePluralization(),
                $v->getCode()
            );
        }

        return $properList;
    }
}
