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
use Grace\SQLBuilder\Factory;
use Grace\SQLBuilder\SelectBuilder;
use Grace\DBAL\ExceptionNoResult as ExceptionNoResultDB;
use Grace\CRUD\ExceptionNoResult as ExceptionNoResultCRUD;

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

    const TABLE_ALIAS = 'Resource';
    /**
     * New instance of SelectBuilder
     * @return \Grace\SQLBuilder\SelectBuilder
     */
    public function getSelectBuilder()
    {
        return (new Factory($this))->select($this->tableName)->setFromAlias(self::TABLE_ALIAS)->setAdditionalTables($this->getAdditionalTables());
    }
    protected function getAdditionalTables()
    {
        return array();
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

        $tables = $this->getAdditionalTables();
        if (count($tables) > 0) {
            array_unshift($tables, $this->tableName);

            $row = null;

            foreach ($tables as $table) {
                try {
                    $row = $this->crud->selectById($table, $id);
                    if ($row) {
                        break;
                    }
                } catch (ExceptionNoResultCRUD $e) {
                    ;
                }
            }

            if (!$row) {
                return false;
            }
        } else {
            try {
                $row = $this->crud->selectById($this->tableName, $id);
            } catch (ExceptionNoResultCRUD $e) {
                return false;
            }
        }

        return $this->convertRowToRecord($row, false);
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