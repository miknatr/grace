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
abstract class FinderCrud extends StaticAware
{
    protected $fullCollectionClassName;
    protected $fullClassName;
    protected $mapper;
    protected $tableName;

    protected $crud;

    /**
     * @param MapperInterface $mapper
     * @param $tableName
     * @param $fullClassName
     * @param $fullCollectionClassName
     */
    final public function __construct(MapperInterface $mapper, $tableName,
                                $fullClassName, $fullCollectionClassName)
    {

        $this->fullClassName           = $fullClassName;
        $this->fullCollectionClassName = $fullCollectionClassName;
        $this->mapper                  = $mapper;
        $this->tableName               = $tableName;
    }
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
    /**
     * Fetches collection of records
     * @return Collection
     */
    public function fetchAll()
    {
        $rows = $this->crud->selectAll($this->tableName);
        $records = array();
        foreach ($rows as $row) {
            $records[] = $this->convertRowToRecord($row, false);
        }
        $collectionClassName = $this->fullCollectionClassName;
        return new $collectionClassName($records);
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
        if ($idWhenNecessary !== null) {
            $fields['id'] = $idWhenNecessary;
        } else {
            $fields['id'] = $this->generateNewId();
        }
        //TODO magic string 'id'
        return $this->convertRowToRecord($fields, true, $newParams);
    }
    /**
     * Generate new id for insert
     * @return mixed
     */
    protected function generateNewId()
    {
        throw new ExceptionUndefinedBehavior('You must implement this method if you need create operations');
    }
    /**
     * Converts db row to record object
     * @param array $row
     * @param       $isNew
     * @return Record
     */
    protected function convertRowToRecord(array $row, $isNew, array $newParams = array())
    {
        $recordArray = $this->mapper->convertDbRowToRecordArray($row);
        $recordClass = $this->fullClassName;
        //TODO magic string 'id'
        $record = new $recordClass($recordArray['id'], $recordArray, $isNew, $newParams);
        $this->getIdentityMap()->setRecord($this->tableName, $record->getId(), $record);
        return $record;
    }
}