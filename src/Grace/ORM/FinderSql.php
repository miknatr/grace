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
use Grace\DBAL\ExceptionNoResult as ExceptionNoResultDB;

/**
 * Finds records by id
 * Gets collections
 * Create new records
 */
abstract class FinderSql extends FinderCrud implements InterfaceExecutable, InterfaceResult
{
    //DB CONNECTIONS

    private $sqlReadOnly;
    /**
     * @param \Grace\DBAL\InterfaceConnection $sqlReadOnly
     */
    public function setSqlReadOnly(InterfaceConnection $sqlReadOnly)
    {
        $this->sqlReadOnly = $sqlReadOnly;
    }



    //IMPLEMETATIONS OF InterfaceExecutable, InterfaceResult

    /** @var InterfaceResult */
    private $queryResult;
    /**
     * Fetches record object
     * @throws ExceptionNoResult
     * @return bool|Record
     */
    public function fetchOne()
    {
        try {
            $row = $this->queryResult->fetchOne();
        } catch (ExceptionNoResultDB $e) {
            throw new ExceptionNoResult($e->getMessage());
        }

        return $this->convertRowToRecord($row, false);
    }
    /**
     * @throws LogicException
     */
    public function fetchOneOrFalse()
    {
        try {
            $row = $this->queryResult->fetchOne();
        } catch (ExceptionNoResultDB $e) {
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
        if (!is_object($this->queryResult)) {
            return $this->getSelectBuilder()->fetchAll();
        }

        $records = array();
        while ($row = $this->queryResult->fetchOneOrFalse()) {
            $records[] = $this->convertRowToRecord($row, false);
        }
        $collectionClassName = $this->getClassNameProvider()->getCollectionClass($this->tableName);
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
        if (!is_object($this->queryResult)) {
            return $this->getSelectBuilder()->fetchAll();
        }

        return $this->queryResult->fetchColumn();
    }
    /**
     * @inheritdoc
     */
    final public function fetchHash()
    {
        if (!is_object($this->queryResult)) {
            return $this->getSelectBuilder()->fetchAll();
        }

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
    final public function getSelectBuilder()
    {
        return new SelectBuilder($this->tableName, $this);
    }



    //NEW ID GENERATION

    protected $idCounter = null;
    /**
     * Generate new id for insert
     * @return mixed
     */
    protected function generateNewId()
    {
        //TODO перенести бы в слой DB, тогда будет логично для постгреса юзать последовательности, а для остальных как повезет
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