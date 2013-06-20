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

    //STOPPER модель в супер грейсе зависит от интертосного юзера
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
    public function __call($name, array $arguments)
    {
        $prefix       = substr($name, 0, 3);
        $propertyName = lcfirst(substr($name, 3));

        if ($prefix == 'get') {
            if (isset($this->orm->config->models[$this->getBaseClass()]->properties[$propertyName])) {
                return $this->getProperty($propertyName);
            }

            if ($this->orm->config->models[$this->getBaseClass()]->parents[$propertyName . 'Id']) {
                return $this->getParent($propertyName);
            }

            throw new PropertyNotFoundException();
        }

        if ($prefix == 'set') {
            if (count($arguments) != 1) {
                // TODO proper exceptions everywhere in grace
                throw new \InvalidArgumentException();
            }

            $this->setProperty($propertyName, $arguments[0]);

            return $this;
        }

        throw new PropertyNotFoundException("Property not found: {$name}");
    }
    final public function getProperty($name)
    {
        return $this->properties[$name];
    }
    final public function setProperty($name, $value)
    {
        if ($name == 'id' or !isset($this->orm->config->models[$this->getBaseClass()]->properties[$name])) {
            throw new \InvalidArgumentException('WTF is this');
        }

        $type = $this->orm->config->models[$this->getBaseClass()]->properties[$name]->mapping->localPropertyType;
        if (!$type) {
            throw new \InvalidArgumentException('Cannot set the unsettable');
        }

        $this->properties[$name] = $this->orm->typeConverter->convertOnSetter($type, $value);
        $this->markAsChanged();

        return $this;
    }
    final public function getParent($name)
    {
        $parentModelName = $this->orm->config->models[$this->getBaseClass()]->parents[$name . 'Id']->parentModel;
        return $this->orm->getFinder($parentModelName)->getByIdOrFalse($this->properties[$name . 'Id']);
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
