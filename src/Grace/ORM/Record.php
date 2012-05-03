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
        }
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
        return $this;
    }
    /**
     * Marks record as changed
     * @return Record
     */
    final public function save()
    {
        $this->unitOfWork->markAsChanged($this);
        return $this;
    }
    /**
     * Marks record as new
     * @return Record
     */
    final public function insert()
    {
        $this->unitOfWork->markAsNew($this);
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