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

/**
 * Finds records by id
 * Gets collections
 * Create new records
 */
abstract class FinderPhpfile extends FinderCrud implements InterfaceExecutable, InterfaceResult
{
    private $sqlReadOnly;
    private $idCounter = null;
    /** @var InterfaceResult */
    private $queryResult;

    /**
     * @param \Grace\DBAL\InterfaceConnection $sqlReadOnly
     */
    public function setSqlReadOnly(InterfaceConnection $sqlReadOnly)
    {
        $this->sqlReadOnly = $sqlReadOnly;
    }
    /**
     * Fetches record object
     * @return bool|Record
     */
    public function fetchOne()
    {
        $row = $this->queryResult->fetchOne();
        if (!is_array($row)) {
            return false;
        }
        return $this->convertRowToRecord($row, false);
    }
    /**
     * Fetches collection of records
     * @return Collection
     */
    public function fetchAll()
    {
        $records = array();
        while ($record = $this->fetchOne()) {
            $records[] = $record;
        }
        $collectionClassName = $this->fullCollectionClassName;
        return new $collectionClassName($records);
    }
    /**
     * @inheritdoc
     */
    final public function fetchResult()
    {
        return $this->queryResult->fetchResult();
    }
    /**
     * @inheritdoc
     */
    final public function fetchColumn()
    {
        return $this->queryResult->fetchColumn();
    }
    /**
     * @inheritdoc
     */
    final public function fetchHash()
    {
        return $this->queryResult->fetchHash();
    }
    /**
     * @inheritdoc
     */
    final public function execute($query, array $arguments = array())
    {
        if (empty($this->sqlReadOnly)) {
            throw new ExceptionUndefinedConnection('SQLReadOnly connection is not defined');
        }
        $this->queryResult = $this->sqlReadOnly->execute($query, $arguments);
        return $this;
    }
    /**
     * New instance of SelectBuilder
     * @return \Grace\SQLBuilder\SelectBuilder
     */
    final protected function getSelectBuilder()
    {
        return new SelectBuilder($this->tableName, $this);
    }
    /**
     * Generate new id for insert
     * @return mixed
     */
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
}