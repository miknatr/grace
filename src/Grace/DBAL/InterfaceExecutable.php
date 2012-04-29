<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\DBAL;

/**
 * Provides executable interface
 */
interface InterfaceExecutable
{
    /**
     * Executes sql query and return InterfaceResult object
     * @abstract
     * @param string $query
     * @param array  $arguments
     * @throws ExceptionQuery, ExceptionConnection
     * @return InterfaceResult
     */
    public function execute($query, array $arguments = array());
}