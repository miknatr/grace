<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Service;

use Grace\ORM\RecordAbstract;

/**
 * Guarantees only one instance of every record
 */
class IdentityMap
{

    private $records = array();

    /**
     * Cleans cache
     * @return \Grace\ORM\Service\IdentityMap
     */
    public function clean()
    {
        $this->records = array();
        return $this;
    }
    /**
     * Gets record
     * @param $class
     * @param $id
     * @return RecordAbstract|bool
     */
    public function getRecord($class, $id)
    {
        $id = self::filterId($id);
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
     * @return \Grace\ORM\Service\IdentityMap
     */
    public function setRecord($class, $id, $record)
    {
        $id = self::filterId($id);
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
        $id = self::filterId($id);
        return isset($this->records[$class][$id]);
    }
    /**
     * Delete record from map
     * @param $class
     * @param $id
     * @return \Grace\ORM\Service\IdentityMap
     */
    public function unsetRecord($class, $id)
    {
        $id = self::filterId($id);
        unset($this->records[$class][$id]);
        return $this;
    }
    private static function filterId($id)
    {
        //чтобы мы могли использовать и int и строки в качестве айди при создании объекта
        $id = strval($id);
        return $id;
    }
}
