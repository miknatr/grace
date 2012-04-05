<?php

namespace Grace\ORM;

use Grace\DBAL\InterfaceConnection;
use Grace\DBAL\InterfaceExecutable;
use Grace\DBAL\InterfaceResult;
use Grace\SQLBuilder\SelectBuilder;

class Finder implements FinderInterface, InterfaceExecutable, InterfaceResult {

    private $fullCollectionClassName;
    private $identityMap;
    private $readConnection;
    private $mapper;
    private $className;
    /** @var InterfaceResult */
    private $queryResult;

    final public function __construct(IdentityMap $identityMap,
        InterfaceConnection $readConnection, MapperInterface $mapper, $className,
        $fullCollectionClassName) {
        $this->fullCollectionClassName = $fullCollectionClassName;
        $this->identityMap = $identityMap;
        $this->readConnection = $readConnection;
        $this->mapper = $mapper;
        $this->className = $className;
    }
    final public function execute($query, array $arguments = array()) {
        $this->queryResult = $this->readConnection->execute($query, $arguments);
    }
    final public function fetchOne() {
        $row = $this->queryResult->fetchOne();
        if (!is_array($row)) {
            return false;
        }
        $record = $this->mapper->convertDbRowToRecordArray($row);
        $this->identityMap->setRecord($this->className, $record->getId(),
            $record);
        return $record;
    }
    final public function fetchAll() {
        $records = array();
        while ($record = $this->fetchOne()) {
            $records[] = $record;
        }
        $collectionClassName = $this->fullCollectionClassName;
        return new $collectionClassName($records);
    }
    final public function fetchResult() {
        return $this->queryResult->fetchResult();
    }
    final public function fetchColumn() {
        return $this->queryResult->fetchColumn();
    }
    final protected function getSelectBuilder() {
        return new SelectBuilder($table, $this);
    }
    final public function getById($id) {
        if ($this->identityMap->issetRecord($this->className, $id)) {
            return $this->identityMap->getRecord($this->className, $id);
        }

        $record = $this->getSelectBuilder()->eq('id', $id)->fetchOne();
        if (!is_object($record)) {
            throw new ExceptionNotFoudById('Row ' . $id . ' in ' . $this->className . ' is not found by id');
        }
        return $record;
    }
}