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
use Grace\DBAL\InterfaceExecutable;
use Grace\DBAL\InterfaceResult;
use Grace\SQLBuilder\SelectBuilder;

abstract class Finder implements FinderInterface, InterfaceExecutable, InterfaceResult
{
    private $fullCollectionClassName;
    private $fullClassName;
    private $eventDispatcher;
    private $unitOfWork;
    private $identityMap;
    private $sqlReadOnly;
    private $crud;
    private $mapper;
    private $className;
    private $idCounter = null;
    /** @var InterfaceResult */
    private $queryResult;

    final public function __construct($eventDispatcher, UnitOfWork $unitOfWork, IdentityMap $identityMap,
                                      MapperInterface $mapper, $className, $fullClassName, $fullCollectionClassName,
                                      InterfaceConnection $sqlReadOnly = null, CRUDInterface $crud = null)
    {

        $this->fullClassName           = $fullClassName;
        $this->fullCollectionClassName = $fullCollectionClassName;
        $this->eventDispatcher         = $eventDispatcher;
        $this->unitOfWork              = $unitOfWork;
        $this->identityMap             = $identityMap;
        $this->sqlReadOnly             = $sqlReadOnly;
        $this->crud                    = $crud;
        $this->mapper                  = $mapper;
        $this->className               = $className;
    }
    final protected function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
    protected function generateNewId()
    {
        //TODO многопоточность мертва
        if ($this->idCounter === null) {
            $this->idCounter = $this
                ->getSelectBuilder()
                ->fields('id')
                ->order('id DESC')
                ->limit(0, 1)
                ->fetchResult();
        }
        return ++$this->idCounter;
    }
    final public function create()
    {
        $id = $this->generateNewId();
        //TODO magic string 'id'
        return $this->convertRowToRecord(array('id' => $id), true);
    }
    final public function getById($id)
    {
        if (empty($this->crud)) {
            throw new ExceptionUndefinedConnection('CRUD connection is not defined');
        }

        if ($this->identityMap->issetRecord($this->className, $id)) {
            return $this->identityMap->getRecord($this->className, $id);
        }

        $row = $this->crud->selectById($this->className, $id);
        if (!is_array($row)) {
            throw new ExceptionNotFoundById('Row ' . $id . ' in ' . $this->className . ' is not found by id');
        }
        $record = $this->convertRowToRecord($row, false);
        return $record;
    }
    final public function fetchOne()
    {
        $row = $this->queryResult->fetchOne();
        if (!is_array($row)) {
            return false;
        }
        return $this->convertRowToRecord($row, false);
    }
    final public function fetchAll()
    {
        $records = array();
        while ($record = $this->fetchOne()) {
            $records[] = $record;
        }
        $collectionClassName = $this->fullCollectionClassName;
        return new $collectionClassName($records);
    }
    final public function fetchResult()
    {
        return $this->queryResult->fetchResult();
    }
    final public function fetchColumn()
    {
        return $this->queryResult->fetchColumn();
    }
    final public function execute($query, array $arguments = array())
    {
        if (empty($this->sqlReadOnly)) {
            throw new ExceptionUndefinedConnection('SQLReadOnly connection is not defined');
        }
        $this->queryResult = $this->sqlReadOnly->execute($query, $arguments);
        return $this;
    }
    final protected function getSelectBuilder()
    {
        return new SelectBuilder($this->className, $this);
    }
    private function convertRowToRecord(array $row, $isNew)
    {
        $recordArray = $this->mapper->convertDbRowToRecordArray($row);
        $recordClass = $this->fullClassName;
        //TODO magic string 'id'
        $record = new $recordClass($this->eventDispatcher, $this->unitOfWork, $recordArray['id'], $recordArray, $isNew);
        $this->identityMap->setRecord($this->className, $record->getId(), $record);
        return $record;
    }
}