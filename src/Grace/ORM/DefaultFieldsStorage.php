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
class DefaultFieldsStorage
{

    private $defaultFields = array();

    /**
     * Cleans cache
     * @return DefaultFieldsStorage
     */
    public function clean()
    {
        $this->defaultFields = array();
        return $this;
    }
    /**
     * Gets record
     * @param $class
     * @param $id
     * @return array|bool
     */
    public function getFields($class, $id)
    {
        $id = self::filterId($id);
        if (!isset($this->defaultFields[$class][$id])) {
            return false;
        }
        return $this->defaultFields[$class][$id];
    }
    /**
     * Sets record into map
     * @param $class
     * @param $id
     * @param $record
     * @return DefaultFieldsStorage
     */
    public function setFields($class, $id, $record)
    {
        $id = self::filterId($id);
        $this->defaultFields[$class][$id] = $record;
        return $this;
    }
    /**
     * Checks if record exist in map
     * @param $class
     * @param $id
     * @return bool
     */
    public function issetFields($class, $id)
    {
        $id = self::filterId($id);
        return isset($this->defaultFields[$class][$id]);
    }
    /**
     * Delete record from map
     * @param $class
     * @param $id
     * @return DefaultFieldsStorage
     */
    public function unsetFields($class, $id)
    {
        $id = self::filterId($id);
        unset($this->defaultFields[$class][$id]);
        return $this;
    }
    private static function filterId($id)
    {
        //чтобы мы могли использовать и int и строки в качестве айди при создании объекта
        $id = strval($id);
        return $id;
    }
}
