<?php

namespace Grace\ORM;

class Mapper implements MapperInterface
{
    //Access to low db level
    final public function getDb()
    {
        return Db_Connector::get();
    }

    //Getting information about current object from model-record
    const ID_FIELD = 'id';
    protected $_className = '';
    protected $_fields = array();
    public function __construct($classname)
    {
        //get only public properties
        $this->_className = $classname;
        $this->_fields = array_keys(get_class_vars($classname));
    }
    public function filterRowFields(array $row)
    {
        $returtRow = array();
        foreach ($this->_fields as $field) {
            $returnRow[$field] = (isset($row[$field]) ? $row[$field] : null);
        }
        return $returnRow;
    }

    //Access by id
    protected function _byIdSql()
    {
        if (is_array(self::ID_FIELD)) {
            return join('=? AND ', self::ID_FIELD) . '=?';
        } else {
            return self::ID_FIELD . '=?';
        }
    }
    public function getRowById($id)
    {
        return $this->getDb()
            ->execute('SELECT * FROM `' . $this->_className . '` WHERE ' . $this->_byIdSql(), $id)
            ->fetchRow();
    }
    public function byId($id)
    {
        if (Model_IdMap::issetRecord($this->_className, $id)) {
            return Model_IdMap::getRecord($this->_className, $id);
        } else {
            $row = $this->getRowById($id);
            if (is_array($row)) {
                $class = $this->_className;
                $record = new $class($this->filterRowFields($row));
                Model_IdMap::setRecord($this->_className, $id, $record);
                return $record;
            }
        }
        return null;
    }
    public function updateById($id, array $row, $allFields = false)
    {
        $record = $this->byId($id);

        if (is_object($record)) {
            if (in_array('onUpdate', get_class_methods($record))) {
                $record = $record->onUpdate($row);
            }

            $currectRow = $record->asArray();

            $fieldsWithPlaceholders = array();
            $values = array();
            foreach ($this->_fields as $property) {
                if (isset($row[$property])) {
                    $value = $row[$property];
                    if (($allFields or $value != $currectRow[$property])
                        and $property != self::ID_FIELD
                    ) {
                        $fieldsWithPlaceholders[] = $property . '=?';
                        $values[] = $value;
                        $record->$property = $value;
                    }
                }
            }

            if (count($values) > 0) {
                $values[] = $id;
                $this->getDb()
                     ->execute('UPDATE `' . $this->_className . '` SET ' . implode(', ', $fieldsWithPlaceholders)
                             . ' WHERE ' . $this->_byIdSql(), $values);
            }
        }
    }
    public function deleteById($id)
    {
        $record = $this->byId($id);

        if (is_object($record)) {
            if (in_array('onDelete', get_class_methods($record))) {
                $record->onDelete();
            }

            $this->getDb()->execute('DELETE FROM `' . $this->_className . '` WHERE ' . $this->_byIdSql(), $id);
            Model_IdMap::unsetRecord($this->_className, $id);
        }
    }

    //Insertion
    public function insert(array $row, $id = null)
    {
        $fields = $placeholders = $values = array();

        if ($id !== null) {
            $fields[] = self::ID_FIELD;
            $placeholders[] = '?';
            $values[] = $id;
        }

        foreach ($this->_fields as $property) {
            if (isset($row[$property])) {
                $value = $row[$property];
                if ($value !== null and $property != self::ID_FIELD) {
                    $fields[] = $property;
                    $placeholders[] = '?';
                    $values[] = $value;
                }
            }
        }

        $this->getDb()
             ->execute('INSERT INTO `' . $this->_className
                     . '` (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')',
                       $values);

        $id = $this->getDb()->getLastQueryProps()->getInsertId();
        $this->_lastInsertedId = $id;

        $record = $this->byId($id);

        /*if (in_array('onInsert', get_class_methods($record))) {
            $newRecord = $record->onInsert($record);
            if ($newRecord !== null and $newRecord != $record) {
                $record->edit($newRecord);
            }
        }*/

        return $record;
    }
    protected $_lastInsertedId = null;
    public function lastInserted()
    {
        return $this->byId($this->_lastInsertedId);
    }

    //Getting by query
    public function allByQuery($query, $args = array())
    {
        $rows = $this->getDb()->execute($query, $args)->fetchAll();
        $records = array();
        foreach ($rows as $row) {
            $id = $row[self::ID_FIELD];
            if (!Model_IdMap::issetRecord($this->_className, $id)) {
                $class = $this->_className;
                Model_IdMap::setRecord($this->_className, $id, new $class($this->filterRowFields($row)));
            }
            $records[] = Model_IdMap::getRecord($this->_className, $id);
        }
        return $records;
    }
    public function oneByQuery($query, $args = array())
    {
        $rows = $this->getDb()->execute($query, $args)->fetchAll();
        if (isset($rows[0])) {
            $row = $rows[0];
            $id = $row[self::ID_FIELD];
            if (!(Model_IdMap::issetRecord($this->_className, $id))) {
                $class = $this->_className;
                Model_IdMap::setRecord($this->_className, $id, new $class($this->filterRowFields($row)));
            }
            return Model_IdMap::getRecord($this->_className, $id);
        } else {
        	return false;
        }
    }

    //Access by condition object
    public function byCond()
    {
        return new Model_Condition($this);
    }
    public function all(Model_Condition $cond = null)
    {
        if (!is_object($cond)) {
            $cond = new Model_Condition($this);
        }
        return $this->allByQuery('SELECT * FROM `' . $this->_className . '` '
                                . $cond->getWhereSql() . ' '. $cond->getOrderSql() . ' ' . $cond->getLimitSql(),
                                  $cond->getArgs());
    }
    public function one(Model_Condition $cond = null)
    {
        if (!is_object($cond)) {
            $cond = new Model_Condition($this);
        }
        return $this->oneByQuery('SELECT * FROM `' . $this->_className . '` '
                                . $cond->getWhereSql() . ' ' . $cond->getOrderSql() . ' LIMIT 0,1',
                                  $cond->getArgs());
    }
    public function count(Model_Condition $cond = null)
    {
        if (!is_object($cond)) {
            $cond = new Model_Condition($this);
        }

        return $this->getDb()
                    ->execute('SELECT COUNT(' . self::ID_FIELD . ') FROM `' . $this->_className . '` '
                            . $cond->getWhereSql() . '',
                              $cond->getArgs())
                    ->fetchResult();
    }
    public function update($row, Model_Condition $cond = null)
    {
        $records = $this->all($cond);
        foreach($records as $record) {
        	$this->updateById($record->{self::ID_FIELD}, $row);
        }
    }
    public function delete(Model_Condition $cond = null)
    {
        $records = $this->all($cond);
        foreach($records as $record) {
        	$this->deleteById($record->{self::ID_FIELD});
        }
    }
    public function column($column, Model_Condition $cond = null)
    {
        if (!is_object($cond)) {
            $cond = new Model_Condition($this);
        }
        $rows = $this->getDb()->execute('SELECT `' . self::ID_FIELD . '`, `' . $column . '` FROM `' . $this->_className . '` '
                                      . $cond->getWhereSql() . ' '. $cond->getOrderSql() . ' ' . $cond->getLimitSql(),
                                        $cond->getArgs())->fetchAll();
        $r = array();
        foreach ($rows as $row) {
            $r[$row[self::ID_FIELD]] = $row[$column];
        }
        return $r;
    }
}
