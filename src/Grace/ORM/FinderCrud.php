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

use Grace\DBAL\InterfaceConnection;
use Grace\CRUD\CRUDInterface;
use Grace\CRUD\CRUDWithAllInterface;
use Grace\CRUD\ExceptionNoResult as ExceptionNoResultCRUD;

/**
 * Finds records by id
 * Gets collections
 * Create new records
 */
abstract class FinderCrud
{
    protected $tableName;

    /**
     * @param $tableName
     */
    final public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }
    public function getTableName(Record $record)
    {
        return $this->tableName;
    }


    //SERVICES GETTERS (one service - one method, access via getOrm()->getService() is not allowed for dependency control reasons)

    /**
     * @return ManagerAbstract
     */
    final protected function getOrm()
    {
        return ManagerAbstract::getCurrent();
    }

    /**
     * @return ServiceContainerInterface
     */
    final protected function getContainer()
    {
        return ManagerAbstract::getCurrent()->getContainer();
    }

    /**
     * @return ClassNameProviderInterface
     */
    final protected function getClassNameProvider()
    {
        return ManagerAbstract::getCurrent()->getClassNameProvider();
    }

    /**
     * @return IdentityMap
     */
    final protected function getIdentityMap()
    {
        return ManagerAbstract::getCurrent()->getIdentityMap();
    }



    //DB CONNECTIONS

    /**
     * @var \Grace\CRUD\CRUDInterface
     */
    protected $crud;
    /**
     * @param \Grace\CRUD\CRUDInterface $crud
     */
    public function setCrud(CRUDInterface $crud)
    {
        if (!($this instanceof FinderSql) and !($crud instanceof CRUDWithAllInterface)) {
            throw new \LogicException('If it is not a FinderSql instance, it needs CRUDWithAllInterface instance');
        }
        $this->crud = $crud;
    }



    //FINDER METHODS

    /**
     * Fetches collection of records
     * @return Record[]
     */
    public function fetchAll()
    {
        $rows = $this->crud->selectAll($this->tableName);
        $records = array();
        foreach ($rows as $row) {
            $records[] = $this->convertRowToRecord($row, false);
        }
        return $records;
    }
    /**
     * Fetches record object
     * @param $id
     * @return Record
     * @throws ExceptionUndefinedConnection
     * @throws ExceptionNoResult
     */
    public function getById($id)
    {
        $recordOrFalse = $this->getByIdOrFalse($id);
        if ($recordOrFalse) {
            return $recordOrFalse;
        } else {
            throw new ExceptionNoResult('Row ' . $id . ' in ' . $this->tableName . ' is not found by id');
        }
    }
    /**
     * Fetches record object
     * @param $id
     * @return Record|bool
     * @throws ExceptionUndefinedConnection
     */
    public function getByIdOrFalse($id)
    {
        if (empty($this->crud)) {
            throw new ExceptionUndefinedConnection('CRUD connection is not defined');
        }

        if ($this->getIdentityMap()->issetRecord($this->tableName, $id)) {
            return $this->getIdentityMap()->getRecord($this->tableName, $id);
        }

        try {
            $row = $this->crud->selectById($this->tableName, $id);
        } catch (ExceptionNoResultCRUD $e) {
            return false;
        }

        return $this->convertRowToRecord($row, false);
    }
    /**
     * Creates new record instance
     * @return Record
     */
    public function create(array $newParams = array(), $idWhenNecessary = null)
    {
        $fields = array();
        //TODO magic string 'id'
        if ($idWhenNecessary !== null) {
            $fields['id'] = $idWhenNecessary;
        } else {
            $fields['id'] = $this->generateNewId();
        }

        return $this->convertRowToRecord($fields, true, $newParams);
    }
    /**
     * Converts db row to record object
     * @param array $row
     * @param       $isNew
     * @return Record
     */
    protected function convertRowToRecord(array $row, $isNew, array $newParams = array())
    {
        $recordClass = $this->getClassNameProvider()->getModelClass($this->tableName);

        //TODO magic string 'id'

        //if already exists in IdentityMap -  we get from IdentityMap because we don't want different objects related to one db row
        $identityMap = $this->getIdentityMap();
        if ($identityMap->issetRecord($this->tableName, $row['id'])) {
            $record = $identityMap->getRecord($this->tableName, $row['id']);
        } else {
            $record = new $recordClass($row['id'], $row, $isNew, $newParams);
            $identityMap->setRecord($this->tableName, $row['id'], $record);
        }

        return $record;
    }



    //NEW ID GENERATION

    /**
     * Generate new id for insert
     * @return mixed
     */
    protected function generateNewId()
    {
        throw new ExceptionUndefinedBehavior('You must implement this method if you need create operations');
    }
}
