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
use Grace\DBAL\ConnectionAbstract\SqlDialectAbstract;
use Grace\SQLBuilder\SelectBuilder;

abstract class FinderAbstract implements ExecutableInterface, ResultInterface
{
    protected $baseClass;
    protected $orm;
    /** @var ResultInterface */
    private $queryResult;

    public function __construct($baseClass, Grace $orm)
    {
        $this->baseClass = $baseClass;
        $this->orm       = $orm;
    }

    /** @return Grace */
    public function getOrm()
    {
        return $this->orm;
    }


    //IMPLEMENTATIONS OF InterfaceExecutable, InterfaceResult

    /**
     * @return SqlDialectAbstract
     */
    public function provideSqlDialect()
    {
        return $this->orm->db->provideSqlDialect();
    }

    /** @return ModelAbstract|bool */
    public function fetchOneOrFalse()
    {
        if ($row = $this->queryResult->fetchOneOrFalse()) {
            return $this->getFromIdentityMapOrMakeModel($row);
        }

        return false;
    }

    /**
     * @throws \LogicException
     * @return ModelAbstract[]
     */
    public function fetchAll()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $models = array();
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $this->queryResult->fetchOneOrFalse()) {
            $models[] = $this->getFromIdentityMapOrMakeModel($row);
        }

        $this->queryResult = null;

        return $models;
    }

    public function fetchResult()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchResult();
        $this->queryResult = null;

        return $r;
    }

    public function fetchHash()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchHash();
        $this->queryResult = null;

        return $r;
    }

    public function fetchColumn()
    {
        if (!is_object($this->queryResult)) {
            throw new \LogicException('Unprepared sql select result');
        }

        $r = $this->queryResult->fetchColumn();
        $this->queryResult = null;

        return $r;
    }

    public function execute($query, array $arguments = array())
    {
        $this->queryResult = $this->orm->db->execute($query, $arguments);
        return $this;
    }

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
            $modelClass = $this->orm->classNameProvider->getModelClass($this->baseClass);
            $model = new $modelClass(null, $dbArray, $this->orm);
            $this->orm->identityMap->setModel($this->baseClass, $dbArray['id'], $model);
        }

        return $model;
    }



    // MODEL SEARCH

    /**
     * @throws \LogicException
     * @return SelectBuilder
     */
    public function getSelectBuilder()
    {
        $selectBuilderClass = $this->getOrm()->classNameProvider->getSelectBuilderClass($this->baseClass);
        /** @var SelectBuilder $selectBuilder */
        $selectBuilder = new $selectBuilderClass($this->baseClass, $this);

        $fields = array();
        $aliases = array();
        foreach ($this->orm->config->models[$this->baseClass]->properties as $propName => $propertyConfig) {
            if ($propertyConfig->mapping->localPropertyType or $propertyConfig->mapping->foreignKeyTable) {
                $fields[] = $propName;
            } else if ($propertyConfig->mapping->relationLocalProperty) {
                $foreignTable = $this->orm->config->models[$this->baseClass]->properties[$propertyConfig->mapping->relationLocalProperty]->mapping->foreignKeyTable;
                $foreignField = $propertyConfig->mapping->relationForeignProperty;

                if (!isset($aliases[$foreignTable])) {
                    $alias = ucfirst(substr($propName, 0, -2)); // ownerId => Owner
                    $selectBuilder
                        ->join($foreignTable, $alias)
                        ->onEq($propertyConfig->mapping->relationLocalProperty, 'id');
                    $aliases[$foreignTable] = $alias;
                }
                $fields[] = array('?f as ?f', array("{$aliases[$foreignTable]}.{$foreignField}", $propName));
            } else {
                throw new \LogicException("Bad mapping in $this->baseClass:$propName");
            }
        }

        $selectBuilder->fields($fields);

        return $selectBuilder;
    }

    /**
     * @param $id
     * @return ModelAbstract|bool
     */
    public function getByIdOrFalse($id)
    {
        if ($this->orm->identityMap->issetModel($this->baseClass, $id)) {
            return $this->orm->identityMap->getModel($this->baseClass, $id);
        }

        // db array is already converted to model object in fetchOneOrFalse
        return $this->getSelectBuilder()->eq('id', $id)->fetchOneOrFalse();
    }

    /**
     * @param array $properties
     * @return ModelAbstract|bool
     * @throws \LogicException
     */
    public function create(array $properties = array())
    {
        //TODO magic string 'id'
        if (!array_key_exists('id', $properties)) {
            $id = $this->orm->db->generateNewId($this->baseClass);
        } else {
            $id = $properties['id'];
        }

        if ($this->orm->identityMap->issetModel($this->baseClass, $id)) {
            throw new \LogicException('Model with id ' . $id . ' already exists in identity map');
        }

        $modelClass = $this->orm->classNameProvider->getModelClass($this->baseClass);
        /** @var ModelAbstract $model */
        $model = new $modelClass($id, null, $this->orm);
        $model->setProperties($properties);

        $this->orm->identityMap->setModel($this->baseClass, $id, $model);
        $this->orm->unitOfWork->markAsNew($model);

        return $model;
    }



    //MAPPING

    protected function convertModelToDbArray(ModelAbstract $model)
    {
        $modelArray = $model->getProperties();
        $dbArray = array();
        foreach ($this->orm->config->models[$this->baseClass]->properties as $propertyName => $propertyConfig) {
            $mapping = $propertyConfig->mapping;

            if ($mapping->localPropertyType) {
                $dbArray[$propertyName] = $this->orm->typeConverter->convertPhpToDb($mapping->localPropertyType, $modelArray[$propertyName]);
            } elseif ($mapping->foreignKeyTable) {
                $type = $this->orm->config->models[$mapping->foreignKeyTable]->properties['id']->mapping->localPropertyType;
                $dbArray[$propertyName] = $this->orm->typeConverter->convertPhpToDb($type, $modelArray[$propertyName], true);
            }
        }
        return $dbArray;
    }

    protected function convertModelToDbChangesArray(ModelAbstract $model)
    {
        $modelArray = $model->getProperties();
        $modelArrayDefaults = $model->getDefaultProperties();

        $dbChangesArray = array();
        foreach ($this->orm->config->models[$this->baseClass]->properties as $propertyName => $propertyConfig) {
            if ($modelArray[$propertyName] != $modelArrayDefaults[$propertyName]) {
                continue;
            }

            $mapping = $propertyConfig->mapping;

            if ($mapping->localPropertyType) {
                $dbChangesArray[$propertyName] = $this->orm->typeConverter->convertPhpToDb($mapping->localPropertyType, $modelArray[$propertyName]);
            } elseif ($mapping->foreignKeyTable) {
                $type = $this->orm->config->models[$this->baseClass]->properties['id']->mapping->localPropertyType;
                $dbArray[$propertyName] = $this->orm->typeConverter->convertPhpToDb($type, $modelArray[$propertyName], true);
            }
        }

        return $dbChangesArray;
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
}
