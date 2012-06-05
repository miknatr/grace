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

/**
 * Gets access to unit of work
 */
abstract class RecordAware extends StaticAware
{
    static private $unitOfWork;

    /**
     * @static
     * @param UnitOfWork $unitOfWork
     */
    final static public function setUnitOfWork(UnitOfWork $unitOfWork)
    {
        self::$unitOfWork = $unitOfWork;
    }
    /**
     * Gets service container
     * @return ServiceContainerInterface
     */
    final protected function getUnitOfWork()
    {
        return self::$unitOfWork;
    }
}