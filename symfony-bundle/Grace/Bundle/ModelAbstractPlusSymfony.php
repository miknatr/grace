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
use Grace\ORM\Type\ConversionImpossibleException;
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

    public function revert()
    {
        $this->conversionViolations = array();
        parent::revert();
    }


    // собираем ошибки конверсии и на ensureValid превращаем их в ошибки валидации вместе со всеми
    /** @var ConstraintViolation[] */
    protected $conversionViolations = array();
    public function setProperty($name, $value)
    {
        try {
            parent::setProperty($name, $value);
        } catch (ConversionImpossibleException $e) {
            $this->conversionViolations[] = new ConstraintViolation(
                $e->getMessage(),
                array(),
                $this,
                $name, // last defined property
                $value // last defined value
            );
        }

        return $this;
    }

    /**
     * @param bool $isAlreadyInitialized
     * @throws Validator\ValidationException
     * @return $this
     */
    public function ensureValid($isAlreadyInitialized = false)
    {
        if ($isAlreadyInitialized) {
            $this->needsInitCreatedModel = false;
        }

        $constraintViolationList = $this->orm->validator->validate($this);

        foreach ($this->conversionViolations as $violation) {
            $constraintViolationList->add($violation);
        }

        if ($constraintViolationList->count() != 0) {
            $this->revert();
            throw new ValidationException($constraintViolationList);
        }

        if ($this->needsInitCreatedModel) {
            $this->initCreatedModel();
            $this->needsInitCreatedModel = false;
        }

        $this->conversionViolations = array();

        return $this;
    }
}
