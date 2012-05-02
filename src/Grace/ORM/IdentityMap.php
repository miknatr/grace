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
 * Guarantees only one instance of every record
 */
class IdentityMap
{

    private $records = array();

    /**
     * Gets record
     * @param $class
     * @param $id
     * @return Record|bool
     */
    public function getRecord($class, $id)
    {
        if (!isset($this->records[$class][$id])) {
            return false;
        }
        return $this->records[$class][$id];
    }
    /**
     * Sets record into map
     * @param $class
     * @param $id
     * @param $record
     * @return IdentityMap
     */
    public function setRecord($class, $id, $record)
    {
        $this->records[$class][$id] = $record;
        return $this;
    }
    /**
     * Checks if record exist in map
     * @param $class
     * @param $id
     * @return bool
     */
    public function issetRecord($class, $id)
    {
        return isset($this->records[$class][$id]);
    }
    /**
     * Delete record from map
     * @param $class
     * @param $id
     * @return IdentityMap
     */
    public function unsetRecord($class, $id)
    {
        unset($this->records[$class][$id]);
        return $this;
    }
}
