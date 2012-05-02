<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\SQLBuilder;

use Grace\DBAL\InterfaceExecutable;

/**
 * Factory for sql-builders
 */
class Factory
{
    private $executable;
    /**
     * @param \Grace\DBAL\InterfaceExecutable $executable
     */
    public function __construct(InterfaceExecutable $executable)
    {
        $this->executable = $executable;
    }
    /**
     * @throws ExceptionCallOrder
     */
    public function execute()
    {
        throw new ExceptionCallOrder('It is factory class, please use select/insert/update/delete/create methods to get concrete sql builders');
    }
    /**
     * @param $table
     * @return SelectBuilder
     */
    public function select($table)
    {
        return new SelectBuilder($table, $this->executable);
    }
    /**
     * @param $table
     * @return InsertBuilder
     */
    public function insert($table)
    {
        return new InsertBuilder($table, $this->executable);
    }
    /**
     * @param $table
     * @return UpdateBuilder
     */
    public function update($table)
    {
        return new UpdateBuilder($table, $this->executable);
    }
    /**
     * @param $table
     * @return DeleteBuilder
     */
    public function delete($table)
    {
        return new DeleteBuilder($table, $this->executable);
    }
}

