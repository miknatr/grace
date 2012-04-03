<?php

class Model_IdMap
{
    static public $_records = array();
    static public $_mappers = array();

    static public function getRecord($class, $id)
    {
        return self::$_records[$class][$id];
    }
    static public function setRecord($class, $id, &$object)
    {
        self::$_records[$class][$id] = $object;
    }
    static public function issetRecord($class, $id)
    {
        return isset(self::$_records[$class][$id]);
    }
    static public function unsetRecord($class, $id)
    {
        unset(self::$_records[$class][$id]);
    }

    static public function getMapper($class)
    {
        if (!isset(self::$_mappers[$class])) {
            $mclass = $class::getMapperName();
            self::$_mappers[$class] = new $mclass($class);
        }
        return self::$_mappers[$class];
    }
}
