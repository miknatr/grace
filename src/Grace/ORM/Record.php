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
abstract class Record implements MapperRecordInterface
{
    private $orm;
    private $container;
    private $unitOfWork;
    private $id;
    private $defaultFields = array();
    protected $fields = array();

    /**
     * @param ManagerAbstract           $orm
     * @param ServiceContainerInterface           $services
     * @param UnitOfWork $unitOfWork
     * @param            $id
     * @param array      $fields
     * @param            $isNew
     */
    final public function __construct(ManagerAbstract $orm, ServiceContainerInterface $container,
                                      UnitOfWork $unitOfWork,
                                      $id, array $fields, $isNew)
    {

        $this->container = $container;
        $this->orm = $orm;
        $this->unitOfWork      = $unitOfWork;

        $this->id            = $id;
        $this->defaultFields = $fields;
        $this->fields        = $fields;

        if ($isNew) { //if it is a new object
            $this->fields = $this->prepareNewFields($this->fields);
            $this->unitOfWork->markAsNew($this);
        }

        $this->init();
    }
    /**
     * Prepares fields for new record
     * @param array $fields
     * @return array
     */
    protected function prepareNewFields(array $fields)
    {
        return $fields;
    }
    /**
     * Init trigger
     */
    protected function init()
    {
        ;
    }
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
        $this->unitOfWork->revert($this);
        $this->fields = $this->getDefaultFields();

        return $this;
    }
    /**
     * Marks record as delete
     * @return Record
     */
    final public function delete()
    {
        $this->unitOfWork->markAsDeleted($this);

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
        $this->unitOfWork->markAsChanged($this);
        return $this;
    }
    /**
     * Gets service container
     * @return ServiceContainerInterface
     */
    final protected function getContainer()
    {
        return $this->container;
    }
    /**
     * Gets orm manager
     * @return ManagerAbstract
     */
    final protected function getOrm()
    {
        return $this->orm;
    }
}