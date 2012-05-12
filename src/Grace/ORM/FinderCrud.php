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
use Grace\CRUD\ExceptionNoResult as ExceptionNoResultCRUD;

/**
 * Finds records by id
 * Gets collections
 * Create new records
 */
abstract class FinderCrud extends Aware
{
    protected $fullCollectionClassName;
    protected $fullClassName;
    protected $unitOfWork;
    protected $identityMap;
    protected $mapper;
    protected $tableName;

    protected $crud;

    /**
     * @param UnitOfWork $unitOfWork
     * @param IdentityMap $identityMap
     * @param MapperInterface $mapper
     * @param $tableName
     * @param $fullClassName
     * @param $fullCollectionClassName
     */
    final public function __construct(UnitOfWork $unitOfWork, IdentityMap $identityMap, MapperInterface $mapper, $tableName,
                                $fullClassName, $fullCollectionClassName)
    {

        $this->fullClassName           = $fullClassName;
        $this->fullCollectionClassName = $fullCollectionClassName;
        $this->unitOfWork              = $unitOfWork;
        $this->identityMap             = $identityMap;
        $this->mapper                  = $mapper;
        $this->tableName               = $tableName;
    }
    /**
     * @param \Grace\CRUD\CRUDInterface $crud
     */
    public function setCrud(CRUDInterface $crud)
    {
        $this->crud = $crud;
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
        if (empty($this->crud)) {
            throw new ExceptionUndefinedConnection('CRUD connection is not defined');
        }

        if ($this->identityMap->issetRecord($this->tableName, $id)) {
            return $this->identityMap->getRecord($this->tableName, $id);
        }

        try {
            $row = $this->crud->selectById($this->tableName, $id);
        } catch (ExceptionNoResultCRUD $e) {
            throw new ExceptionNoResult('Row ' . $id . ' in ' . $this->tableName . ' is not found by id');
        }

        return $this->convertRowToRecord($row, false);
    }
    /**
     * Creates new record instance
     * @return Record
     */
    public function create()
    {
        $id = $this->generateNewId();
        //TODO magic string 'id'
        return $this->convertRowToRecord(array('id' => $id), true);
    }
    /**
     * Generate new id for insert
     * @return mixed
     */
    protected function generateNewId()
    {
        throw new ExceptionUndefinedBehavior('You mus implement this method if you need create operations');
    }
    /**
     * Converts db row to record object
     * @param array $row
     * @param       $isNew
     * @return Record
     */
    protected function convertRowToRecord(array $row, $isNew)
    {
        $recordArray = $this->mapper->convertDbRowToRecordArray($row);
        $recordClass = $this->fullClassName;
        //TODO magic string 'id'
        $record =
            new $recordClass($this->getOrm(), $this->getContainer(), $this->unitOfWork, $recordArray['id'], $recordArray, $isNew);
        $this->identityMap->setRecord($this->tableName, $record->getId(), $record);
        return $record;
    }
}