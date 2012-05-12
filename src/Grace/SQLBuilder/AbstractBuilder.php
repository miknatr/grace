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
use Grace\DBAL\InterfaceResult;

/**
 * Provides some base functions for builders
 */
abstract class AbstractBuilder implements InterfaceResult
{
    /** @var InterfaceExecutable */
    private $executable;
    protected $from;

    /**
     * @param                                 $fromTable
     * @param \Grace\DBAL\InterfaceExecutable $executable
     */
    public function __construct($fromTable, InterfaceExecutable $executable)
    {
        $this->from       = $fromTable;
        $this->executable = $executable;
    }
    /**
     * @return InterfaceResult
     */
    public function execute()
    {
        return $this->executable->execute($this->getQueryString(), $this->getQueryArguments());
    }
    /**
     * @inheritdoc
     */
    public function fetchAll()
    {
        return $this
            ->execute()
            ->fetchAll();
    }
    /**
     * @inheritdoc
     */
    public function fetchOneOrFalse()
    {
        return $this
            ->execute()
            ->fetchOneOrFalse();
    }
    /**
     * @inheritdoc
     */
    public function fetchOne()
    {
        return $this
            ->execute()
            ->fetchOne();
    }
    /**
     * @inheritdoc
     */
    public function fetchResult()
    {
        return $this
            ->execute()
            ->fetchResult();
    }
    /**
     * @inheritdoc
     */
    public function fetchColumn()
    {
        return $this
            ->execute()
            ->fetchColumn();
    }
    /**
     * @inheritdoc
     */
    public function fetchHash()
    {
        return $this
            ->execute()
            ->fetchHash();
    }
    /**
     * @abstract
     * @return string sql query string
     */
    abstract protected function getQueryString();
    /**
     * @abstract
     * @return array arguments for sql query
     */
    abstract protected function getQueryArguments();
}

