<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\CRUD;

use Grace\DBAL\InterfaceConnection;
use Grace\DBAL\ExceptionNoResult as ExceptionNoResultDB;

/**
 * Master crud db connection
 */
class DBMasterDriver implements CRUDInterface, CRUDCommitableInterface
{
    private $connection;

    /**
     * @param \Grace\DBAL\InterfaceConnection $connection
     */
    public function __construct(InterfaceConnection $connection)
    {
        $this->connection = $connection;
    }
    /**
     * @inheritdoc
     */
    public function selectById($table, $id)
    {
        try {
            return $this->connection
                ->getSQLBuilder()
                ->select($table)
                ->eq('id', $id)
                ->fetchOne();
        } catch (ExceptionNoResultDB $e) {
            throw new ExceptionNoResult($e->getMessage());
        }
    }
    /**
     * @inheritdoc
     */
    public function insertById($table, $id, array $values)
    {
        $values['id'] = $id;
        return $this->connection
            ->getSQLBuilder()
            ->insert($table)
            ->values($values)
            ->execute();
    }
    /**
     * @inheritdoc
     */
    public function updateById($table, $id, array $values)
    {
        return $this->connection
            ->getSQLBuilder()
            ->update($table)
            ->values($values)
            ->eq('id', $id)
            ->execute();
    }
    /**
     * @inheritdoc
     */
    public function deleteById($table, $id)
    {
        return $this->connection
            ->getSQLBuilder()
            ->delete($table)
            ->eq('id', $id)
            ->execute();
    }
    /**
     * @inheritdoc
     */
    public function start()
    {
        return $this->connection->execute('START TRANSACTION');
    }
    /**
     * @inheritdoc
     */
    public function commit()
    {
        return $this->connection->execute('COMMIT');
    }
    /**
     * @inheritdoc
     */
    public function rollback()
    {
        return $this->connection->execute('ROLLBACK');
    }
}

