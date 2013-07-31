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

    //it's public properties for optimization reasons
    public $baseClass;
    public $id;

    // TODO переделать на фабрики вместо безумия с null-аргументами
    public function __construct($id = null, array $dbArray = null, $baseClass, Grace $orm)
    {
        $this->baseClass = $baseClass;
        $this->orm = $orm;

        //$dbArray - model creation from database
        //$id - new model creation
        //both can't be filled
        if (!($dbArray !== null xor $id !== null)) {
            throw new \LogicException('Invalid model initialization');
        }

        if ($dbArray === null) {
            $this->setDefaultPropertyValues();
            $type = $this->orm->config->models[$this->baseClass]->properties['id']->type;
            $this->properties['id'] = $this->orm->typeConverter->convertOnSetter($type, $id);
        } else {
            $this->setPropertiesFromDbArray($dbArray);
        }
        $this->id = $this->properties['id'];

        $this->originalProperties = $this->properties;
    }

    abstract protected function setPropertiesFromDbArray(array $dbArray);

    /**
     * Делаем значения для пустой модели (только что создали, никаких данных ещё нет и в БД её нет)
     */
    abstract protected function setDefaultPropertyValues();


    //
    // INTERNALS
    //

    final public function getProperties()
    {
        return $this->properties;
    }

    /**
     * DO NOT USE THIS
     * @param array $values
     */
    final public function setRawProperties($values)
    {
        // TODO может это убрать в интертосный ModelAbstract
        $this->properties = $values;
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
        if ($name == 'id') {
            throw new \InvalidArgumentException('Cannot set ID of a model');
        }

        if (!isset($this->orm->config->models[$this->baseClass]->properties[$name])) {
            throw new \InvalidArgumentException("Cannot set unknown property: {$this->baseClass}.{$name}");
        }

        $propConfig = $this->orm->config->models[$this->baseClass]->properties[$name];
        if (!$propConfig->isSettable) {
            throw new \LogicException("Cannot set a readonly property: {$this->baseClass}.{$name}");
        }

        $type = $propConfig->type;
        try {
            $this->properties[$name] = $this->orm->typeConverter->convertOnSetter(
                $type,
                $value,
                $propConfig->isNullable
            );
        } catch (ConversionImpossibleException $e) {
            throw new ConversionImpossibleException($e->getMessage() . " in {$this->baseClass} when setting {$name}", $e->getCode(), $e);
        }

        // при вызове например setRegionId мы должны помимо поля regionId ещё проставить
        // в модели поля, которые подтягиваются по связи через это поле (например regionName)
        foreach ($propConfig->dependentProxies as $propName =>$proxy) {
            if ($value === null) {
                $this->properties[$proxy->localProperty] = null;
            } else {
                $foreignModel = $this->orm->getFinder($proxy->foreignModel)->getByIdOrFalse($value);
                if (!$foreignModel) {
                    throw new \Exception("Cannot set {$this->baseClass}.{$name}: there is no {$proxy->foreignModel} with ID {$value}");
                }

                $this->properties[$propName] = $foreignModel->getProperty($proxy->foreignProperty);
            }
        }

        $this->markAsChanged();

        return $this;
    }

    /**
     * Array of original model properties (i.e. before editing)
     * @return array
     */
    public function getOriginalProperties()
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
