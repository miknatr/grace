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

    /** @var \Grace\DBAL\InterfaceConnection */
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
     * Get symbol for escaping SQL data such as tables and columns.
     * @return string
     */
    public function getSqlEscapeSymbol()
    {
        $this->sqlReadOnly->getSqlEscapeSymbol();
    }

    /**
     * Get symbol for escaping strings
     * @return string
     */
    public function getDataEscapeSymbol()
    {
        $this->sqlReadOnly->getDataEscapeSymbol();
    }

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
     * @inheritdoc
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

    /**
     * Generate new id for insert
     * @return mixed
     */
    protected function generateNewId()
    {
        return $this->sqlReadOnly->generateNewId($this->tableName);
    }
}
