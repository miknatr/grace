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

use Grace\DBAL\ConnectionAbstract\ExecutableInterface;

/**
 * Factory for sql-builders
 */
class Factory
{
    //STOPPER выпилить
    /**
     * @var string
     */
    static private $namespacePrefix;

    /**
     * @param string $customSelectBuilderPrefix
     */
    public static function setNamespacePrefix($customSelectBuilderPrefix)
    {
        self::$namespacePrefix = $customSelectBuilderPrefix;
    }
    /**
     * @return string
     */
    public static function getNamespacePrefix()
    {
        return self::$namespacePrefix;
    }


    private $executable;
    /**
     * @param ExecutableInterface $executable
     */
    public function __construct(ExecutableInterface $executable)
    {
        $this->executable = $executable;
    }
    /**
     * @param $table
     * @return SelectBuilder
     */
    public function select($table)
    {
        $class = '\\' . self::$namespacePrefix . '\\SelectBuilder\\' . $table . 'SelectBuilder';
        if (class_exists($class)) {
            return new $class($table, $this->executable);
        } else {
            return new SelectBuilder($table, $this->executable);
        }
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

