<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\DBAL\AbstractConnection;

use Grace\DBAL\AbstractConnection\ResultInterface;
use Grace\DBAL\Exception\QueryException;

/**
 * Provides executable interface
 */
interface ExecutableInterface
{
    /**
     * Executes sql query and return InterfaceResult object
     * @abstract
     * @param string $query
     * @param array  $arguments
     * @throws \Grace\DBAL\Exception\QueryException, ExceptionConnection
     * @return ResultInterface
     */
    public function execute($query, array $arguments = array());
}