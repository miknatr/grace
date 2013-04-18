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

/**
 * Base model class
 */
abstract class RecordAbstract
{
    protected $orm;
    private $id;
    //STOPPER возможно тепреь это будут приватные штуки?
    protected $fields = array();
    protected $defaults = array();

    final public function __construct(array $fields, ORMManagerAbstract $orm)
    {
        $this->orm      = $orm;

        //TODO id в константу бы на уровне орм
        if (!isset($fields['id'])) {
            throw new \LogicException('Id field is not given');
        }

        $this->id       = $fields['id'];
        $this->defaults = $fields;
        $this->fields   = $fields;
    }
    final public function getBaseClass()
    {
        //optimization, caching baseClass in static variable
        static $baseClasses = array();
        $class = get_class($this);

        if (!isset($baseClasses[$class])) {
            $baseClasses[$class] = $this->orm->classNameProvider->getBaseClass($class);
        }

        return $baseClasses[$class];
    }
    final public function getOriginalRecord()
    {
        $class = get_class($this);
        return new $class($this->defaults);
    }
    final public function getId()
    {
        return $this->id;
    }
    final public function getFields()
    {
        return $this->fields;
    }
    final public function getDefaults()
    {
        return $this->defaults;
    }
    final public function flushDefaults()
    {
        $this->defaults = $this->fields;
    }
    final public function revertChanges()
    {
        $this->orm->unitOfWork->revert($this);
        $this->fields = $this->defaults;
    }
    final public function delete()
    {
        $this->orm->unitOfWork->markAsDeleted($this);
    }
    //STOPPER если сетеры выпиливать в пользу edit, то это приватная вещь
    final protected function markAsChanged()
    {
        $this->orm->unitOfWork->markAsChanged($this);
    }
    final public function edit(array $fields)
    {
        foreach ($fields as $k => $v) {
            $method = 'set' . ucfirst($k);
            if (method_exists($this, $method)) {
                $this->$method($v);
            }
        }
        $this->markAsChanged();
    }
    final public function __sleep()
    {
        throw new \LogicException('Model serializing breaks links to orm services and is not supported');
    }
}
