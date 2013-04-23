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

use Grace\ORM\ModelAbstract;

/**
 * Guarantees only one instance of every model
 */
class IdentityMap
{

    private $models = array();

    /**
     * Cleans cache
     * @return \Grace\ORM\Service\IdentityMap
     */
    public function clean()
    {
        $this->models = array();
        return $this;
    }
    /**
     * Gets model
     * @param $class
     * @param $id
     * @return ModelAbstract|bool
     */
    public function getModel($class, $id)
    {
        $id = self::filterId($id);
        if (!isset($this->models[$class][$id])) {
            return false;
        }
        return $this->models[$class][$id];
    }
    /**
     * Sets model into map
     * @param $class
     * @param $id
     * @param $model
     * @return \Grace\ORM\Service\IdentityMap
     */
    public function setModel($class, $id, $model)
    {
        $id = self::filterId($id);
        $this->models[$class][$id] = $model;
        return $this;
    }
    /**
     * Checks if model exist in map
     * @param $class
     * @param $id
     * @return bool
     */
    public function issetModel($class, $id)
    {
        $id = self::filterId($id);
        return isset($this->models[$class][$id]);
    }
    /**
     * Delete model from map
     * @param $class
     * @param $id
     * @return \Grace\ORM\Service\IdentityMap
     */
    public function unsetModel($class, $id)
    {
        $id = self::filterId($id);
        unset($this->models[$class][$id]);
        return $this;
    }
    private static function filterId($id)
    {
        //чтобы мы могли использовать и int и строки в качестве айди при создании объекта
        $id = strval($id);
        return $id;
    }
}
