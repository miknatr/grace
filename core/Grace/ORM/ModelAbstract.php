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
    private $defaultProperties = array();
    protected $properties = array();

    final public function __construct($id = null, array $dbArray = null, Grace $orm = null)
    {
        $this->orm = $orm;

        //$dbArray - model creation from database
        //$id - new model creation
        //both can't be filled
        if (!($dbArray !== null xor $id !== null)) {
            throw new \Exception;
        }

        if ($dbArray == null) {
            $this->setPropertiesNull();

            $type = $this->orm->config->models[$this->getBaseClass()]->properties['id']->mapping->localPropertyType;
            $this->properties['id'] = $this->orm->typeConverter->convertOnSetter($type, $id);
        } else {
            $this->setPropertiesFromDbArray($dbArray);
        }

        $this->defaultProperties = $this->properties;
    }

    private function setPropertiesFromDbArray(array $dbArray)
    {
        $baseClass = $this->getBaseClass();

        $properties = array();
        foreach ($this->orm->config->models[$baseClass]->properties as $propertyName => $propertyConfig) {
            //TODO вызов метода на каждое поле потенциально медленное место, проверить бы скорость и может оптимизировать
            if ($propertyConfig->mapping->localPropertyType) {
                $properties[$propertyName] = $this->orm->typeConverter->convertDbToPhp($propertyConfig->mapping->localPropertyType, $dbArray[$propertyName]);
            } elseif ($propertyConfig->mapping->relationForeignProperty) {
                // если поле задано как проброс чужого поля по связи, выковыриваем тип этого поля
                $modelConfig       = $this->orm->config->models[$baseClass];
                $foreignBaseClass  = $modelConfig->parents[$propertyConfig->mapping->relationLocalProperty]->parentModel;
                $parentModelConfig = $this->orm->config->models[$foreignBaseClass];
                $type = $parentModelConfig->properties[$propertyConfig->mapping->relationForeignProperty]->mapping->localPropertyType;
                if (!$type) {
                    throw new \LogicException("Property {$foreignBaseClass}.{$propertyConfig->mapping->relationForeignProperty} must be defined with local mapping");
                }
                $properties[$propertyName] = $this->orm->typeConverter->convertDbToPhp($type, $dbArray[$propertyName]);
            } else {
                throw new \LogicException("Bad mapping in $baseClass:$propertyName");
            }
        }

        $this->properties = $properties;
    }

    private function setPropertiesNull()
    {
        $baseClass = $this->getBaseClass();

        $properties = array();
        foreach ($this->orm->config->models[$baseClass]->properties as $propertyName => $propertyConfig) {
            if ($propertyConfig->mapping->localPropertyType or $propertyConfig->mapping->relationForeignProperty) {
                $properties[$propertyName] = null;
            } else {
                throw new \LogicException("Bad mapping in $baseClass:$propertyName");
            }
        }

        $this->properties = $properties;
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
        return $this->getProperty('id');
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
