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
use Grace\ORM\Type\ConversionImpossibleException;
use Intertos\CoreBundle\Security\Core\User\UserAbstract;

/**
 * Base model class
 */
abstract class ModelAbstract
{
    //TODO выпилить бы это автодополнение в плагин
    /** @var Grace|GracePlusSymfony */
    protected $orm;
    private $originalProperties = array();
    protected $properties = array();

    public function __construct($id = null, array $dbArray = null, Grace $orm)
    {
        $this->orm = $orm;

        //$dbArray - model creation from database
        //$id - new model creation
        //both can't be filled
        if (!($dbArray !== null xor $id !== null)) {
            throw new \Exception('Invalid model initialization');
        }

        if ($dbArray === null) {
            $this->setDefaultPropertyValues();

            $type = $this->orm->config->models[$this->getBaseClass()]->properties['id']->type;
            $this->properties['id'] = $this->orm->typeConverter->convertOnSetter($type, $id);
        } else {
            $this->setPropertiesFromDbArray($dbArray);
        }

        $this->originalProperties = $this->properties;
    }

    private function setPropertiesFromDbArray(array $dbArray)
    {
        $baseClass = $this->getBaseClass();

        $properties = array();
        foreach ($this->orm->config->models[$baseClass]->properties as $propertyName => $propertyConfig) {
            //TODO вызов метода на каждое поле потенциально медленное место, проверить бы скорость и может оптимизировать
            $properties[$propertyName] = $this->orm->typeConverter->convertDbToPhp(
                $propertyConfig->type,
                $dbArray[$propertyName],
                $propertyConfig->isNullable
            );
        }

        $this->properties = $properties;
    }

    /**
     * Делаем значения для пустой модели (только что создали, никаких данных ещё нет и в БД её нет)
     */
    private function setDefaultPropertyValues()
    {
        $baseClass = $this->getBaseClass();

        $properties = array();
        foreach ($this->orm->config->models[$baseClass]->properties as $propertyName => $propertyConfig) {
            $type = $propertyConfig->type;

            if ($propertyConfig->default) {
                $properties[$propertyName] = $this->orm->typeConverter->convertOnSetter($type, $propertyConfig->default->getValue(), $propertyConfig->isNullable);
            } else if ($propertyConfig->isNullable) {
                $properties[$propertyName] = null;
            } else {
                $properties[$propertyName] = $this->orm->typeConverter->getPhpDefaultValue($type);
            }
        }

        $this->properties = $properties;
    }


    //
    // INTERNALS
    //

    final public function getBaseClass()
    {
        return $this->orm->classNameProvider->getBaseClass(get_class($this));
    }
    public function getOriginalModel()
    {
        // TODO кеширование
        $class = get_class($this);
        /** @var ModelAbstract $model */
        $model = new $class($this->getId(), null, $this->orm);
        $model->setProperties($this->originalProperties);
        return $model;
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

        $propConfig = $this->orm->config->models[$this->getBaseClass()]->properties[$name];
        if (!$propConfig->isSettable) {
            throw new \Exception('FUCK OFF');
        }

        $type = $propConfig->type;
        try {
            $this->properties[$name] = $this->orm->typeConverter->convertOnSetter(
                $type,
                $value,
                $propConfig->isNullable
            );
        } catch (ConversionImpossibleException $e) {
            throw new ConversionImpossibleException($e->getMessage() . " in {$this->getBaseClass()} when setting {$name}", $e->getCode(), $e);
        }

        // при вызове например setRegionId мы должны помимо поля regionId ещё проставить
        // в модели поля, которые подтягиваются по связи через это поле (например regionName)
        foreach ($propConfig->dependentProxies as $propName =>$proxy) {
            if ($value === null) {
                $this->properties[$proxy->localField] = null;
            } else {
                $foreignModel = $this->orm->getFinder($proxy->foreignTable)->getByIdOrFalse($value);
                if (!$foreignModel) {
                    throw new \Exception("Cannot set {$this->getBaseClass()}.{$name}: there is no {$proxy->foreignTable} with ID {$value}");
                }

                $this->properties[$propName] = $foreignModel->getProperty($proxy->foreignField);
            }
        }

        $this->markAsChanged();

        return $this;
    }

    /**
     * Array of original model properties (i.e. before editing)
     * @return array
     */
    final public function getOriginalProperties()
    {
        return $this->originalProperties;
    }
    final public function flushDefaults()
    {
        $this->originalProperties = $this->properties;
    }
    final public function revert()
    {
        $this->orm->unitOfWork->revert($this);
        $this->properties = $this->originalProperties;
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
