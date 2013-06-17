<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM;
use Grace\Bundle\GracePlusSymfony;
use Intertos\CoreBundle\Security\Core\User\UserAbstract;

/**
 * Base model class
 */
abstract class ModelAbstract
{
    //TODO выпилить бы это автодополнение в плагин
    /** @var Grace|GracePlusSymfony */
    protected $orm;
    private $id;
    private $defaultProperties = array();
    protected $properties = array();

    final public function __construct(array $properties, Grace $orm = null)
    {
        $this->orm = $orm;

        //TODO id в константу бы на уровне орм
        if (!isset($properties['id'])) {
            throw new \LogicException('Id property is not given');
        }

        $this->id                = $properties['id'];
        $this->defaultProperties = $properties;
        $this->properties        = $properties;
    }


    //
    // OVERRIDE
    //

    public function initCreatedModel(UserAbstract $user = null)
    {
        return $this;
    }


    //
    // INTERNALS
    //

    final public function getBaseClass()
    {
        return $this->orm->classNameProvider->getBaseClass(get_class($this));
    }
    final public function getOriginalModel()
    {
        $class = get_class($this);
        return new $class($this->defaultProperties);
    }
    final public function getId()
    {
        return $this->id;
    }
    final public function getProperties()
    {
        return $this->properties;
    }
    final public function setProperties($values)
    {
        foreach ($values as $property => $value) {
            if ($property != 'id') {
                $methodName = 'set' . ucfirst($property);
                if (method_exists($this, $methodName)) {
                    call_user_func(array($this, $methodName), $value);
                }
            }
        }
        return $this;
    }
    final public function getDefaultProperties()
    {
        return $this->defaultProperties;
    }
    final public function flushDefaults()
    {
        $this->defaultProperties = $this->properties;
    }
    final public function revert()
    {
        $this->orm->unitOfWork->revert($this);
        $this->properties = $this->defaultProperties;
    }
    final public function delete()
    {
        $this->orm->unitOfWork->markAsDeleted($this);
    }
    final public function markAsChanged()
    {
        $this->orm->unitOfWork->markAsChanged($this);
    }
    final public function __sleep()
    {
        throw new \LogicException('Model serializing breaks links to orm services and is not supported');
    }

    /**
     * @return GracePlusSymfony|Grace
     */
    final public function getOrm()
    {
        return $this->orm;
    }
}
