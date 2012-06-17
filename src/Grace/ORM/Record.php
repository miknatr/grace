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
abstract class Record extends RecordAware implements MapperRecordInterface
{
    private $id;
    private $defaultFields = array();
    protected $fields = array();

    /**
     * @param            $id
     * @param array      $fields
     * @param            $isNew
     */
    final public function __construct($id, array $fields, $isNew)
    {
        $this->id            = $id;
        $this->defaultFields = $fields;
        $this->fields        = $fields;

        if ($isNew) { //if it is a new object
            $this->onCreate();
            $this->getUnitOfWork()->markAsNew($this);
        }

        $this->onInit();
    }
    protected function onCreate() {}
    protected function onInit() {}

    /**
     * @inheritdoc
     */
    final public function asArray()
    {
        return $this->fields;
    }
    /**
     * @inheritdoc
     */
    final public function getDefaultFields()
    {
        return $this->defaultFields;
    }
    /**
     * Delete all changes about this record
     * @return Record
     */
    final public function revert()
    {
        $this->getUnitOfWork()->revert($this);
        $this->fields = $this->getDefaultFields();

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
     * Gets id of record
     * @return string
     */
    final public function getId()
    {
        return $this->id;
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