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

use Grace\Bundle\ApiBundle\Model\ResourceAbstract;

/**
 * Base model class
 */
abstract class Record implements ManagerRecordInterface
{
    static protected $fieldNames = array();
    static protected $noDbFieldNames = array();

    /**
     * Converts from db row to record array
     * @abstract
     * @param array $row
     * @return array
     */
    protected function convertDbRowToRecordArray(array $row)
    {
        $recordArray = array();
        foreach (static::$fieldNames as $field) {
            if (isset($row[$field])) {
                $recordArray[$field] = $row[$field];
            } else {
                $recordArray[$field] = null;
            }
        }
        foreach (static::$noDbFieldNames as $field) {
            $recordArray[$field] = null;
        }
        return $recordArray;
    }
    /**
     * Converts from record array to db row
     * @abstract
     * @param array $recordArray
     * @return array
     */
    public function getAsDbRow()
    {
        $recordArray = $this->getFields();
        $row = array();
        foreach (static::$fieldNames as $field) {
            if (isset($recordArray[$field])) {
                $row[$field] = $recordArray[$field];
            } else {
                $row[$field] = null;
            }
        }
        return $row;
    }
    /**
     * Gets differs between record and defaults
     * @abstract
     * @param array $recordArray
     * @param array $defaults
     * @return array
     */
    public function getAsDbRowChangesOnlyAndCleanDefaultFields()
    {
        $recordArray = $this->getFields();
        $defaults = $this->defaults;

        $changes = array();
        foreach (static::$fieldNames as $field) {
            if (isset($recordArray[$field]) and (!isset($defaults[$field]) or $recordArray[$field] != $defaults[$field])) {
                $changes[$field] = $recordArray[$field];
            }
        }

        $this->defaults = $this->getAsDbRow();

        return $changes;
    }


    //SERVICES GETTERS (one service - one method, access via getOrm()->getService() is not allowed for dependency control reasons)

    /**
     * @return ManagerAbstract
     */
    final protected function getOrm()
    {
        return ManagerAbstract::getCurrent();
    }

    /**
     * @return ServiceContainerInterface
     */
    final protected function getContainer()
    {
        return ManagerAbstract::getCurrent()->getContainer();
    }

    /**
     * @return ClassNameProviderInterface
     */
    final protected function getClassNameProvider()
    {
        return ManagerAbstract::getCurrent()->getClassNameProvider();
    }

    /**
     * @return UnitOfWork
     */
    final private function getUnitOfWork()
    {
        return ManagerAbstract::getCurrent()->getUnitOfWork();
    }



    //RECORD METHODS

    private $id;
    private $isNew = false;
    protected $fields = array();
    protected $defaults = array();
    protected $originalRecord;


    /** @return Record */
    public function getOriginalRecord()
    {
        return $this->originalRecord;
    }

    /**
     * @param            $id
     * @param array      $fields
     * @param            $isNew
     */
    final public function __construct($id, array $fields, $isNew, array $newParams = array())
    {
        $this->id     = $id;
        $this->isNew  = $isNew;
        $this->defaults = $fields;
        $this->fields = $this->convertDbRowToRecordArray($fields);

        if ($this->isNew) { //if it is a new object
            $this->onCreate($newParams);
            $this->getUnitOfWork()->markAsNew($this);
        }

        //TODO оптимизировать
        $this->originalRecord = clone $this;

        $this->onInit();
    }
    protected function onCreate(array $params = array()) {}
    protected function onInit() {}

    /**
     * Gets id of record
     * @return string
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function asArray()
    {
        $r = array();
        foreach ($this->fields as $fieldName => $v) {
            $getter = 'get' . ucfirst($fieldName);
            $r[$fieldName] = $this->$getter();
        }
        return $r;
    }
    /**
     * @inheritdoc
     */
    final public function getFields()
    {
        return $this->fields;
    }

    /**
     * @inheritdoc
     */
    public function onCommitInsert() {}
    /**
     * @inheritdoc
     */
    public function onCommitChange() {}
    /**
     * @inheritdoc
     */
    public function onCommitDelete() {}

    /**
     * Delete all changes about this record
     * @return Record
     */
    final public function revert()
    {
        $this->getUnitOfWork()->revert($this);
        $this->fields = $this->defaults;

        return $this;
    }
    /**
     * Marks record as delete
     * @return Record
     */
    public function delete()
    {
        $this->getUnitOfWork()->markAsDeleted($this);

        return $this;
    }
    /**
     * Edits fields
     * Calls setters
     * @param array $fields
     * @return Record
     */
    final public function edit(array $fields)
    {
        foreach ($fields as $k => $v) {
            $method = 'set' . ucfirst($k);
            if (method_exists($this, $method)) {
                $this->$method($v);
            }
        }
        $this->markAsChanged();
        return $this;
    }
    /**
     * Clears is new marker
     * @return Record
     */
    final public function clearIsNewMarker()
    {
        $this->isNew = false;
        return $this;
    }
    /**
     * Marks record as changed
     * @return Record
     */
    public function markAsChanged()
    {
        if (!$this->isNew) {
            $this->getUnitOfWork()->markAsChanged($this);
        }

        return $this;
    }
}
