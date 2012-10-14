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
 * Base model class
 */
abstract class Record implements MapperRecordInterface, ManagerRecordInterface
{

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
     * @return DefaultFieldsStorage
     */
    final private function getDefaultFieldsStorage()
    {
        return ManagerAbstract::getCurrent()->getDefaultFieldsStorage();
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
    protected $fields = array();

    /**
     * @param            $id
     * @param array      $fields
     * @param            $isNew
     */
    final public function __construct($id, array $fields, $isNew, array $newParams = array())
    {
        $this->id     = $id;
        $this->fields = $fields;

        $this->getDefaultFieldsStorage()->setFields(get_class($this), $this->getId(), $fields);

        if ($isNew) { //if it is a new object
            $this->onCreate($newParams);
            $this->getUnitOfWork()->markAsNew($this);
        }

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
        $this->fields = $this->getDefaultFieldsStorage()->getFields(get_class($this), $this->getId());

        return $this;
    }
    /**
     * Marks record as delete
     * @return Record
     */
    final public function delete()
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
     * Marks record as changed
     * @return Record
     */
    final protected function markAsChanged()
    {
        $this->getUnitOfWork()->markAsChanged($this);
        return $this;
    }
}