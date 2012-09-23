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
 * Record interface for mapper
 */
interface MapperRecordInterface
{
    /**
     * @abstract
     * @return array
     */
    public function asArray();
    /**
     * Method for mapper usage only
     * @abstract
     * @return array
     */
    public function getFields();
    /**
     * Method for mapper usage only
     * @abstract
     * @return array
     */
    public function getDefaultFields();
}
