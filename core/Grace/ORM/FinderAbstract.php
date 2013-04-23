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

use Grace\DBAL\ConnectionAbstract\ExecutableInterface;
use Grace\DBAL\ConnectionAbstract\ResultInterface;
use Grace\SQLBuilder\Factory;
use Grace\SQLBuilder\SelectBuilder;

abstract class FinderAbstract implements ExecutableInterface, ResultInterface
{
    protected $baseClass;
    protected $orm;

    public function __construct($baseClass, Grace $orm)
    {
        $this->baseClass = $baseClass;
        $this->orm       = $orm;
    }
    public function getOrm()
    {
        return $this->orm;
    }



    //IMPLEMETATIONS OF InterfaceExecutable, InterfaceResult

    /** @var \Grace\DBAL\ConnectionAbstract\ResultInterface */
    private $queryResult;
    /** @return ModelAbstract|bool */
    final public function fetchOneOrFalse()
    {
        if ($row = $this->queryResult->fetchOneOrFalse()) {
            return $this->getFromIdentityMapOrMakeModel($row);
        }

        return false;
    }
    /** @return ModelAbstract[] */
    final public function fetchAll()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $models = array();
        while ($row = $this->queryResult->fetchOneOrFalse()) {
            $models[] = $this->getFromIdentityMapOrMakeModel($row);
        }

        $this->queryResult = null;

        return $models;
    }
    final public function fetchResult()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchResult();
        $this->queryResult = null;

        return $r;
    }
    final public function fetchHash()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchHash();
        $this->queryResult = null;

        return $r;
    }
    final public function fetchColumn()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchColumn();
        $this->queryResult = null;

        return $r;
    }
    final public function execute($query, array $arguments = array())
    {
        $this->queryResult = $this->orm->db->execute($query, $arguments);
        return $this;
    }



    //SELECT BUILDER CREATION

    const TABLE_ALIAS = 'Resource';
    /** @return SelectBuilder */
    public function getSelectBuilder()
    {
        return (new Factory($this))->select($this->baseClass)->setFromAlias(self::TABLE_ALIAS);
    }



    //MODEL GETTERS

    /**
     * @param $id
     * @return ModelAbstract|bool
     */
    public function getByIdOrFalse($id)
    {
        if ($this->orm->identityMap->issetModel($this->baseClass, $id)) {
            return $this->orm->identityMap->getModel($this->baseClass, $id);
        }

        if ($row = $this->orm->db->getSQLBuilder()->select($this->baseClass)->eq('id', $id)->fetchOneOrFalse()) {
            return $this->getFromIdentityMapOrMakeModel($row);
        }

        return false;
    }


    /**
     * @param array $properties
     * @return ModelAbstract
     */
    //STOPPER поменялась сигнатура
    public function create(array $properties = array())
    {
        //TODO magic string 'id'
        if (!isset($properties['id'])) {
            $properties['id'] = $this->orm->db->generateNewId($this->baseClass);
        }

        if ($this->orm->identityMap->issetModel($this->baseClass, $properties['id'])) {
            throw new \LogicException('Model with id ' . $properties['id'] . ' already exists in identity map');
        }

        $modelClass = $this->orm->classNameProvider->getModelClass($this->baseClass);
        $model = new $modelClass(array('id' => $properties['id']), $this->orm);

        //TODO сомнительно, что этот мэппинг нужно делать именно здесь и через сеттеры
        //STOPPER может вообще избавится от установки полей здесь, как проверять осмысленность модели если нет ключевых полей?
        foreach ($properties as $k => $v) {
            if ($k != 'id') {
                $setterName = 'set' . ucfirst($k);
                if (!method_exists($model, $setterName)) {
                    throw new \LogicException('Setter ' . $setterName . ' is not exist');
                }

                $model->$setterName($v);
            }
        }

        $this->orm->identityMap->setModel($this->baseClass, $properties['id'], $model);
        $this->orm->unitOfWork->markAsNew($model);

        return $model;
    }



    //ON COMMIT EVENTS

    public function insertModelOnCommit(ModelAbstract $model)
    {
        $values = $this->convertModelToDbArray($model);
        $this->orm->db->getSQLBuilder()->insert($this->baseClass)->values($values)->execute();
        $model->flushDefaults();
    }
    public function updateModelOnCommit(ModelAbstract $model)
    {
        $changes = $this->convertModelToDbChangesArray($model);
        if (count($changes) > 0) {
            $this->orm->db->getSQLBuilder()->update($this->baseClass)->values($changes)->eq('id', $model->getId())->execute();
            $model->flushDefaults();
        }
    }
    public function deleteModelOnCommit(ModelAbstract $model)
    {
        $this->orm->db->getSQLBuilder()->delete($this->baseClass)->eq('id', $model->getId())->execute();
    }



    //MAPPING

    /**
     * @param array $dbArray
     * @return ModelAbstract
     */
    protected function getFromIdentityMapOrMakeModel(array $dbArray)
    {
        //TODO magic string 'id'
        //if already exists in IdentityMap -  we get from IdentityMap because we don't want different objects related to one db row
        if ($this->orm->identityMap->issetModel($this->baseClass, $dbArray['id'])) {
            $model = $this->orm->identityMap->getModel($this->baseClass, $dbArray['id']);
        } else {
            $model = $this->convertDbArrayToModel($dbArray);
            $this->orm->identityMap->setModel($this->baseClass, $dbArray['id'], $model);
        }

        return $model;
    }

    /**
     * @abstract
     * @param array $dbArray
     * @return array
     */
    protected function convertDbArrayToModel(array $dbArray)
    {
        $modelArray = array();
        foreach ($this->orm->config->models[$this->baseClass]->properties as $propertyName => $propertyConfig) {
            //TODO вызов метода на каждое поле потенциально медленное место, проверить бы скорость и может оптимизировать
            if ($propertyConfig->mapping) {
                $modelArray[$propertyName] = $this->orm->typeConverter->convertDbToPhp($propertyConfig->mapping, $dbArray[$propertyName]);
            }
        }

        $modelClass = $this->orm->classNameProvider->getModelClass($this->baseClass);
        return new $modelClass($modelArray, $this->orm);
    }

    /**
     * @param ModelAbstract $model
     * @return array
     */
    protected function convertModelToDbArray(ModelAbstract $model)
    {
        $modelArray = $model->getProperties();
        $dbArray = array();
        foreach ($this->orm->config->models[$this->baseClass]->properties as $propertyName => $propertyConfig) {
            //if (isset($modelArray[$propertyName])) {
                $dbArray[$propertyName] = $this->orm->typeConverter->convertPhpToDb($propertyConfig->mapping, $modelArray[$propertyName]);
            //} else {
            //    $dbArray[$propertyName] = null;
            //}
        }
        return $dbArray;
    }

    /**
     * @param ModelAbstract $model
     * @return array
     */
    protected function convertModelToDbChangesArray(ModelAbstract $model)
    {
        $modelArray = $model->getProperties();
        $modelArrayDefaults = $model->getDefaultProperties();

        $dbChangesArray = array();
        foreach ($this->orm->config->models[$this->baseClass]->properties as $propertyName => $propertyConfig) {
            if (/*isset($modelArray[$propertyName]) and */$modelArray[$propertyName] != $modelArrayDefaults[$propertyName]) {
                $dbChangesArray[$propertyName] = $this->orm->typeConverter->convertPhpToDb($propertyConfig->mapping, $modelArray[$propertyName]);
            }
        }

        return $dbChangesArray;
    }
}
