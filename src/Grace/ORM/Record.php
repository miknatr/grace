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
use Grace\TypeConverter\Converter;

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
    //STOPPER эти методы должны переехать в какие то универсальные мэперы, нехуй им здесь делать
    protected function convertDbRowToRecordArray(array $row)
    {
        //STOPPER конфиг лучше объектом с паблик полями, тогда все будет ок пожизни
        $properties = $this->getModelConfig()['properties'];

        $recordArray = array();
        foreach ($properties as $property => $propertyOptions) {
            if ($propertyOptions['mapping']) {
                $this->getTypeConverter()->convertDbToPhp($propertyOptions['mapping'], $row[$property]);
            } else {
                //STOPPER вообще вычисляемые поля должны быть определены именно здесь, но откуда? да мне похуй откуда, здесь и все
                //то что нет доступа на поле или его надо высчитывать от юзера это вопрос апи-мэперов, а не этих
                $recordArray[$property] = null;
            }
        }

        return $recordArray;
    }
    /**
     * Converts from record array to db row
     * @return array
     */
    public function getAsDbRow()
    {
        //STOPPER конфиг лучше объектом с паблик полями, тогда все будет ок пожизни
        $properties = $this->getModelConfig()['properties'];

        $recordArray = $this->getFields();
        $row = array();
        foreach ($properties as $property => $propertyOptions) {
            if ($propertyOptions['mapping']) {
                if (isset($recordArray[$property])) {
                    $this->getTypeConverter()->convertPhpToDb($propertyOptions['mapping'], $recordArray[$property]);
                } else {
                    //STOPPER выбрать стратегию, или дефолт или нулы
                    //$row[$field] = null; //default values in db must be used
                }
            }
        }
        return $row;
    }
    /**
     * Gets differs between record and defaults
     * @return array
     */
    public function getAsDbRowChangesOnlyAndCleanDefaultFields()
    {
        //STOPPER конфиг лучше объектом с паблик полями, тогда все будет ок пожизни
        $properties = $this->getModelConfig()['properties'];

        $recordArray = $this->getFields();
        $defaults = $this->defaults;

        $changes = array();
        foreach ($properties as $property => $propertyOptions) {
            if ($propertyOptions['mapping']) {
                if (isset($recordArray[$property]) and $recordArray[$property] != $defaults[$property]) {
                    $this->getTypeConverter()->convertPhpToDb($propertyOptions['mapping'], $recordArray[$property]);
                }
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

    //STOPPER это бы ваще нахуй отсюда конечно
    /**
     * @return array
     */
    final protected function getModelConfig()
    {
        return ManagerAbstract::getCurrent()->getModelsConfig()[$this->getClassNameProvider()->getBaseClass(get_class($this))];
    }

    //STOPPER это бы ваще нахуй отсюда конечно
    /**
     * @return Converter
     */
    final protected function getTypeConverter()
    {
        return ManagerAbstract::getCurrent()->getTypeConverter();
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
     * @param       $id
     * @param array $fields
     * @param       $isNew
     * @param array $newParams
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
